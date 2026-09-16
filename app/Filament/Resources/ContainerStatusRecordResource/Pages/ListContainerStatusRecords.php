<?php

namespace App\Filament\Resources\ContainerStatusRecordResource\Pages;

use App\Filament\Resources\ContainerStatusRecordResource;
use App\Models\ContainerStatusDetail;
use App\Models\ContainerStatusRecord;
use App\Models\FiscalYear;
use App\Models\Month;
use App\Models\Port;
use App\Services\ActivityLogger;
use App\Services\ContainerExcelImportService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ListContainerStatusRecords extends ListRecords
{
    protected static string $resource = ContainerStatusRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // ─── زر نسخ بيانات ميناء إلى شهر آخر ───
            Action::make('copy_port_month')
                ->label('نسخ ميناء إلى شهر آخر')
                ->icon('heroicon-o-document-duplicate')
                ->color('success')
                ->modalHeading('نسخ وتكرار بيانات موقف الحاويات لميناء إلى شهر آخر')
                ->modalDescription('يتيح هذا الإجراء نسخ جميع قيود وجهات الحاويات وأعدادها من شهر وسنة سابقة إلى شهر جديد عند عدم تغير الأعداد.')
                ->modalSubmitActionLabel('تنفيذ النسخ')
                ->modalIcon('heroicon-o-document-duplicate')
                ->form([
                    Select::make('port_id')
                        ->label('الميناء')
                        ->options(Port::where('is_active', true)->where('has_container_status', true)->orderBy('sort_order')->pluck('name_ar', 'id'))
                        ->required()
                        ->searchable(),

                    Select::make('container_type')
                        ->label('نوع الحاوية')
                        ->options([
                            'abandoned' => '📦 حاويات متخلفة',
                            'dangerous' => '⚠️ حاويات خطرة',
                        ])
                        ->default('abandoned')
                        ->required(),

                    Select::make('source_fiscal_year_id')
                        ->label('السنة المالية (المصدر)')
                        ->options(FiscalYear::orderBy('year', 'desc')->pluck('year', 'id'))
                        ->default(fn () => FiscalYear::where('is_current', true)->first()?->id ?? FiscalYear::orderBy('year', 'desc')->first()?->id)
                        ->required(),

                    Select::make('source_month_id')
                        ->label('الشهر (المصدر)')
                        ->options(Month::orderBy('month_number')->pluck('name_ar', 'id'))
                        ->required(),

                    Select::make('target_fiscal_year_id')
                        ->label('السنة المالية (المستهدفة للنسخ إليها)')
                        ->options(FiscalYear::orderBy('year', 'desc')->pluck('year', 'id'))
                        ->default(fn () => FiscalYear::where('is_current', true)->first()?->id ?? FiscalYear::orderBy('year', 'desc')->first()?->id)
                        ->required(),

                    Select::make('target_month_id')
                        ->label('الشهر (المستهدف للنسخ إليه)')
                        ->options(Month::orderBy('month_number')->pluck('name_ar', 'id'))
                        ->required(),

                    DatePicker::make('target_report_date')
                        ->label('تاريخ التقرير الجديد')
                        ->native(false)
                        ->displayFormat('d/m/Y')
                        ->format('Y-m-d')
                        ->closeOnDateSelection()
                        ->default(now())
                        ->required(),
                ])
                ->action(function (array $data) {
                    $portId = (int) $data['port_id'];
                    $containerType = $data['container_type'];
                    $srcYearId = (int) $data['source_fiscal_year_id'];
                    $srcMonthId = (int) $data['source_month_id'];
                    $targetYearId = (int) $data['target_fiscal_year_id'];
                    $targetMonthId = (int) $data['target_month_id'];
                    $targetReportDate = $data['target_report_date'];

                    // البحث عن سجل المصدر
                    $sourceRecord = ContainerStatusRecord::where('port_id', $portId)
                        ->where('container_type', $containerType)
                        ->where('fiscal_year_id', $srcYearId)
                        ->where('month_id', $srcMonthId)
                        ->with('details')
                        ->first();

                    if (! $sourceRecord) {
                        Notification::make()
                            ->title('تعذر النسخ: سجل المصدر غير موجود')
                            ->body('لا توجد بيانات مسجلة للميناء المحدد في سنة وشهر المصدر لنقلها.')
                            ->danger()
                            ->send();
                        return;
                    }

                    // التحقق من أن السجل المستهدف غير موجود
                    $existing = ContainerStatusRecord::withTrashed()
                        ->where('port_id', $portId)
                        ->where('container_type', $containerType)
                        ->where('fiscal_year_id', $targetYearId)
                        ->where('month_id', $targetMonthId)
                        ->first();

                    if ($existing) {
                        Notification::make()
                            ->title('تنبيه: السجل المستهدف موجود مسبقاً')
                            ->body('يوجد بالفعل سجل محفوظ لهذا الميناء في الشهر والسنة المستهدفين.')
                            ->warning()
                            ->send();
                        return;
                    }

                    DB::transaction(function () use ($sourceRecord, $portId, $containerType, $targetYearId, $targetMonthId, $targetReportDate, $srcYearId, $srcMonthId) {
                        $newRecord = ContainerStatusRecord::create([
                            'port_id'         => $portId,
                            'container_type'  => $containerType,
                            'fiscal_year_id'  => $targetYearId,
                            'month_id'        => $targetMonthId,
                            'report_date'     => $targetReportDate,
                            'notes'           => $sourceRecord->notes,
                            'created_by'      => Auth::id() ?? 1,
                        ]);

                        foreach ($sourceRecord->details as $detail) {
                            ContainerStatusDetail::create([
                                'container_status_record_id' => $newRecord->id,
                                'container_entity_id'        => $detail->container_entity_id,
                                'year_label'                 => $detail->year_label,
                                'count'                      => $detail->count,
                                'sort_order'                 => $detail->sort_order,
                            ]);
                        }

                        ActivityLogger::log(
                            'created',
                            "نسخ سجل موقف الحاويات لميناء ({$sourceRecord->port?->name_ar}) من شهر ({$sourceRecord->month?->name_ar}) إلى شهر جديد",
                            ContainerStatusRecord::class,
                            $newRecord->id,
                            ['source_record_id' => $sourceRecord->id, 'target_year_id' => $targetYearId, 'target_month_id' => $targetMonthId]
                        );
                    });

                    $portName = Port::find($portId)?->name_ar;
                    $targetMonthName = Month::find($targetMonthId)?->name_ar;
                    $targetYearVal = FiscalYear::find($targetYearId)?->year;

                    Notification::make()
                        ->title('تم نسخ السجل بنجاح')
                        ->body("تم نسخ كافة بيانات موقف الحاويات لميناء ({$portName}) إلى شهر {$targetMonthName} {$targetYearVal} بنجاح.")
                        ->success()
                        ->send();
                }),

            // ─── زر استيراد ومقاطعة ملف الإكسل الشهري ───
            Action::make('import_excel')
                ->label('استيراد ومقاطعة ملف الإكسل الشهري')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('warning')
                ->modalHeading('استيراد وتحديث موقف الحاويات الشهري والمقاطعة الذكية مع الشهر السابق')
                ->modalDescription('قم برفع ملف الإكسل الشهري للحاويات (المتخلفة أو الخطرة). سيقوم النظام بقراءة كافة أوراق العمل، ومطابقة الجهات والموانئ، وتحديث أرقام الحاويات، وإجراء مقاطعة آلية مع الشهر السابق لتحديد الحاويات التي تم تخريجها وإخلاؤها وتحديث المصفوفة والإحصائيات تلقائياً.')
                ->modalSubmitActionLabel('بدء الاستيراد والمقاطعة الذكية')
                ->modalIcon('heroicon-o-arrow-up-tray')
                ->form([
                    FileUpload::make('excel_file')
                        ->label('ملف الإكسل (Excel File)')
                        ->acceptedFileTypes([
                            'application/vnd.ms-excel',
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/octet-stream',
                            '.xls',
                            '.xlsx',
                        ])
                        ->disk('public')
                        ->directory('container_imports')
                        ->preserveFilenames()
                        ->required()
                        ->helperText('الملف يمكن أن يحتوي على عدة أوراق عمل لعدة جهات ووزارات'),

                    Select::make('container_type')
                        ->label('نوع الحاويات في الملف')
                        ->options([
                            'abandoned' => '📦 موقف الحاويات المتخلفة',
                            'dangerous' => '⚠️ موقف الحاويات الخطرة',
                        ])
                        ->default('abandoned')
                        ->required(),

                    Select::make('fiscal_year_id')
                        ->label('السنة المالية المستهدفة')
                        ->options(FiscalYear::orderBy('year', 'desc')->pluck('year', 'id'))
                        ->default(fn () => FiscalYear::where('is_current', true)->first()?->id ?? FiscalYear::orderBy('year', 'desc')->first()?->id)
                        ->required(),

                    Select::make('month_id')
                        ->label('الشهر المستهدف')
                        ->options(Month::orderBy('month_number')->pluck('name_ar', 'id'))
                        ->default(fn () => Month::where('month_number', (int) date('n'))->first()?->id)
                        ->required(),

                    DatePicker::make('report_date')
                        ->label('تاريخ الموقف / التقرير')
                        ->native(false)
                        ->displayFormat('d/m/Y')
                        ->format('Y-m-d')
                        ->closeOnDateSelection()
                        ->default(now())
                        ->required(),

                    Select::make('default_port_id')
                        ->label('الميناء الافتراضي (اختياري)')
                        ->options(Port::where('is_active', true)->where('has_container_status', true)->orderBy('sort_order')->pluck('name_ar', 'id'))
                        ->helperText('يُستخدم في حال كانت بعض أسطر الملف لا تحتوي على اسم الميناء صراحة')
                        ->searchable(),
                ])
                ->action(function (array $data) {
                    $uploadedPath = Storage::disk('public')->path($data['excel_file']);

                    try {
                        $service = new ContainerExcelImportService();
                        $stats = $service->import(
                            filePath: $uploadedPath,
                            fiscalYearId: (int) $data['fiscal_year_id'],
                            monthId: (int) $data['month_id'],
                            containerType: $data['container_type'],
                            defaultPortId: !empty($data['default_port_id']) ? (int) $data['default_port_id'] : null,
                            reportDate: $data['report_date'],
                            userId: Auth::id()
                        );

                        $portsCount = count($stats['ports_processed']);
                        $dischargedCount = $stats['discharged_count'];
                        $importedCount = $stats['total_imported'];

                        $portsSummary = [];
                        foreach ($stats['ports_processed'] as $pInfo) {
                            $portsSummary[] = "• {$pInfo['port_name']}: تم استيراد {$pInfo['imported_count']} حاوية (المجموع بالميناء: {$pInfo['final_total']})";
                        }
                        $portsSummaryText = implode("\n", $portsSummary);

                        Notification::make()
                            ->title('تم الاستيراد والمقاطعة بنجاح!')
                            ->body("تمت معالجة {$importedCount} حاوية عبر {$portsCount} موانئ بنجاح.\nالحاويات التي تم تخريجها بمقاطعة الشهر السابق: {$dischargedCount}\n\n{$portsSummaryText}")
                            ->success()
                            ->persistent()
                            ->send();

                        // ─── إشعار خاص في حال وجود مخالفة إضافة حاويات بسنوات سابقة ───
                        if (!empty($stats['prior_year_anomalies'])) {
                            $anomCount = count($stats['prior_year_anomalies']);
                            $anomLines = [];
                            foreach (array_slice($stats['prior_year_anomalies'], 0, 15) as $anom) {
                                $anomLines[] = "• حاوية [{$anom['container_number']}] سنة ({$anom['arrival_year']}) - {$anom['entity_name']} ({$anom['port_name']})";
                            }
                            if ($anomCount > 15) {
                                $anomLines[] = "• ... وهناك المزيد (" . ($anomCount - 15) . " حاوية أخرى)";
                            }
                            $anomText = implode("\n", $anomLines);

                            Notification::make()
                                ->title("⚠️ تنبيه رقابي: تم رصد {$anomCount} حاوية مضافة بسنوات سابقة لم تكن مسجلة في الشهر السابق!")
                                ->body("القاعدة الرقابية: الحاويات للسنوات السابقة يُفترض أن تتناقص بالتخريج فقط ولا تضاف جديدة.\n\nالحاويات المرصودة:\n{$anomText}")
                                ->warning()
                                ->persistent()
                                ->send();
                        }

                        // ─── إشعار خاص في حال وجود اختلاف في تاريخ الوصول المسجل سابقاً ───
                        if (!empty($stats['date_mismatch_anomalies'])) {
                            $dateAnomCount = count($stats['date_mismatch_anomalies']);
                            $dateAnomLines = [];
                            foreach (array_slice($stats['date_mismatch_anomalies'], 0, 15) as $danom) {
                                $dateAnomLines[] = "• حاوية [{$danom['container_number']}]: السابق ({$danom['prev_date']}) ⟵ الحالي ({$danom['curr_date']}) - {$danom['entity_name']} ({$danom['port_name']})";
                            }
                            if ($dateAnomCount > 15) {
                                $dateAnomLines[] = "• ... وهناك المزيد (" . ($dateAnomCount - 15) . " حاوية أخرى)";
                            }
                            $dateAnomText = implode("\n", $dateAnomLines);

                            Notification::make()
                                ->title("⚠️ تنبيه رقابي: تم رصد {$dateAnomCount} حاوية تختلف تواريخ وصولها عن السجلات السابقة!")
                                ->body("تم رصد حاويات مدرجة بتاريخ أو سنة وصول تختلف عما كان مسجلاً لها في الأشهر السابقة:\n\n{$dateAnomText}")
                                ->warning()
                                ->persistent()
                                ->send();
                        }
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('حدث خطأ أثناء معالجة ملف الإكسل')
                            ->body($e->getMessage())
                            ->danger()
                            ->persistent()
                            ->send();
                    }
                }),

            // ─── زر تصدير كشف الحاويات التفصيلي مقسم بحسب الجهات ───
            Action::make('export_detailed_excel')
                ->label('تصدير كشف الحاويات التفصيلي (Excel)')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->modalHeading('تصدير كشف الحاويات الفردية إلى ملف Excel مقسم بحسب الجهات')
                ->modalDescription('سيتم تصدير ملف إكسل يحتوي على أوراق عمل متعددة (لكل وزارة وجهة ورقة عمل مستقلة) بنفس التنسيق الرسمي وبكافة تفاصيل الحاويات الموجودة في الميناء حالياً مع استثناء الحاويات المخرجة.')
                ->modalSubmitActionLabel('تحميل ملف Excel')
                ->modalIcon('heroicon-o-arrow-down-tray')
                ->form([
                    Select::make('container_type')
                        ->label('نوع الموقف المراد تصديره')
                        ->options([
                            'abandoned' => '📦 موقف الحاويات المتخلفة',
                            'dangerous' => '⚠️ موقف الحاويات الخطرة',
                        ])
                        ->default('abandoned')
                        ->required(),

                    Select::make('fiscal_year_id')
                        ->label('السنة المالية')
                        ->options(FiscalYear::orderBy('year', 'desc')->pluck('year', 'id'))
                        ->default(fn () => FiscalYear::where('is_current', true)->first()?->id ?? FiscalYear::orderBy('year', 'desc')->first()?->id)
                        ->required(),

                    Select::make('month_id')
                        ->label('الشهر')
                        ->options(Month::orderBy('month_number')->pluck('name_ar', 'id'))
                        ->default(fn () => Month::where('month_number', (int) date('n'))->first()?->id)
                        ->required(),

                    Select::make('port_id')
                        ->label('الميناء')
                        ->options(Port::where('is_active', true)->where('has_container_status', true)->orderBy('sort_order')->pluck('name_ar', 'id'))
                        ->placeholder('كافة الموانئ (مجمّع)')
                        ->searchable(),
                ])
                ->action(function (array $data) {
                    $queryParams = http_build_query([
                        'type'  => $data['container_type'],
                        'year'  => $data['fiscal_year_id'],
                        'month' => $data['month_id'],
                        'port'  => $data['port_id'] ?? null,
                    ]);

                    return redirect()->away(route('admin.containers.export-detailed-excel') . '?' . $queryParams);
                }),
        ];
    }
}
