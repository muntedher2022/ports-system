<?php

namespace App\Filament\Resources\MonthlyPortRecordResource\Pages;

use App\Exports\PortRecordTemplateExport;
use App\Filament\Resources\MonthlyPortRecordResource;
use App\Imports\PortRecordImport;
use App\Models\FiscalYear;
use App\Models\Month;
use App\Models\Port;
use App\Services\SinglePortMonthlyImportService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Grid;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Enums\Width;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class ListMonthlyPortRecords extends ListRecords
{
    protected static string $resource = MonthlyPortRecordResource::class;

    protected ?string $heading = 'السجلات التشغيلية للموانئ';

    protected function getHeaderActions(): array
    {
        return [
            // ─── قائمة منسدلة جامعة لكافة الإجراءات والخيارات ───
            ActionGroup::make([
                // 1. ميزة الإدخال المباشر (اليدوي)
                CreateAction::make()
                    ->label('إضافة سجل تشغيلي (إدخال يدوي مباشر)')
                    ->icon('heroicon-o-plus')
                    ->color('primary'),

                // 2. ميزة استيراد بيانات شهر معين لميناء معين من Excel
                Action::make('import_single_port_month')
                    ->label('استيراد شهر محدد لميناء من Excel')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('success')
                    ->modalHeading('استيراد بيانات تشغيلية لشهر معين لميناء معين من Excel')
                    ->modalDescription('اختر الميناء والسنة والشهر المطلوبين، ثم ارفع ملف الإكسل ليتم تعبئة كافة الحقول والمؤشرات تلقائياً دون الحاجة للإدخال اليدوي.')
                    ->modalSubmitActionLabel('بدء الاستيراد والحفظ')
                    ->modalIcon('heroicon-o-table-cells')
                    ->modalWidth(Width::Large)
                    ->form([
                        Grid::make(3)->schema([
                            Select::make('port_id')
                                ->label('الميناء المستهدف')
                                ->options(function () {
                                    $user = Auth::user();
                                    if ($user?->isPortRestricted() && $user?->port_id) {
                                        return Port::where('id', $user->port_id)->pluck('name_ar', 'id');
                                    }

                                    return Port::where('is_active', true)
                                        ->where(fn ($q) => $q->where('has_monthly_records', true)->orWhereHas('monthlyPortRecords'))
                                        ->orderBy('sort_order')
                                        ->pluck('name_ar', 'id');
                                })
                                ->default(function () {
                                    $user = Auth::user();
                                    if ($user?->isPortRestricted() && $user?->port_id) {
                                        return $user->port_id;
                                    }

                                    return Port::where('is_active', true)->where('has_monthly_records', true)->orderBy('sort_order')->first()?->id;
                                })
                                ->required()
                                ->searchable(),

                            Select::make('fiscal_year_id')
                                ->label('السنة المالية')
                                ->options(FiscalYear::orderBy('year', 'desc')->pluck('year', 'id'))
                                ->default(FiscalYear::where('is_current', true)->first()?->id ?? FiscalYear::latest('year')->first()?->id)
                                ->required(),

                            Select::make('month_id')
                                ->label('الشهر المستهدف')
                                ->options(Month::orderBy('month_number')->pluck('name_ar', 'id'))
                                ->default(Month::where('month_number', (int) date('n'))->first()?->id ?? Month::first()?->id)
                                ->required(),
                        ]),

                        FileUpload::make('excel_file')
                            ->label('ملف Excel الخاص ببيانات الميناء والشهر')
                            ->helperText('يقبل ملفات Excel (.xlsx, .xls, .csv). يتم قراءة مؤشرات البواخر، الحاويات، البضائع العامة والنفط وحساب المجاميع تلقائياً.')
                            ->acceptedFileTypes([
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                'application/vnd.ms-excel',
                                'text/csv',
                            ])
                            ->required()
                            ->maxSize(51200)
                            ->disk('local')
                            ->directory('imports/single-port-monthly')
                            ->preserveFilenames(false),
                    ])
                    ->action(function (array $data) {
                        $path = storage_path('app/private/'.$data['excel_file']);
                        if (! file_exists($path)) {
                            $path = storage_path('app/'.$data['excel_file']);
                        }

                        $service = app(SinglePortMonthlyImportService::class);

                        try {
                            $res = $service->import(
                                (int) $data['port_id'],
                                (int) $data['fiscal_year_id'],
                                (int) $data['month_id'],
                                $path
                            );

                            Notification::make()
                                ->success()
                                ->title($res['action'] === 'created' ? 'تم استيراد وإنشاء السجل بنجاح ✓' : 'تم تحديث بيانات السجل بنجاح ✓')
                                ->body("ميناء: {$res['port_name']} | شهر: {$res['month_name']} {$res['year']}\nإجمالي البواخر: {$res['total_ships']} | الحاويات TEU: {$res['total_teu']} | البضائع: ".number_format($res['total_cargo']).' طن')
                                ->persistent()
                                ->send();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->danger()
                                ->title('خطأ أثناء استيراد الملف')
                                ->body($e->getMessage())
                                ->persistent()
                                ->send();
                        }
                    }),

                // 3. رفع ملف البيانات الشامل (أشهر وموانئ متعددة)
                Action::make('import_records')
                    ->label('استيراد ملف شامل (عدة موانئ وأشهر)')
                    ->icon('heroicon-o-cloud-arrow-up')
                    ->color('info')
                    ->form([
                        FileUpload::make('excel_file')
                            ->label('ملف Excel الشامل (xlsx/xls/csv)')
                            ->helperText('ارفع الملف المعبأ وفق القالب الشامل لعدة أشهر وموانئ')
                            ->acceptedFileTypes([
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                'application/vnd.ms-excel',
                                'text/csv',
                            ])
                            ->required()
                            ->maxSize(102400)
                            ->disk('local')
                            ->directory('imports/port-records')
                            ->preserveFilenames(false),
                    ])
                    ->modalHeading('رفع بيانات السجلات التشغيلية الشاملة')
                    ->modalDescription('يمكنك رفع بيانات أشهر متعددة وموانئ متعددة في ملف واحد. سيتم إنشاء سجلات جديدة أو تحديث الموجودة تلقائياً.')
                    ->modalSubmitActionLabel('رفع وحفظ')
                    ->modalIcon('heroicon-o-cloud-arrow-up')
                    ->modalWidth(Width::Large)
                    ->action(function (array $data) {
                        $path = storage_path('app/private/'.$data['excel_file']);
                        if (! file_exists($path)) {
                            $path = storage_path('app/'.$data['excel_file']);
                        }

                        $import = new PortRecordImport;

                        try {
                            Excel::import($import, $path);
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->danger()
                                ->title('خطأ في قراءة الملف')
                                ->body($e->getMessage())
                                ->persistent()
                                ->send();

                            return;
                        }

                        $total = $import->importedCount + $import->updatedCount;

                        if (! empty($import->failures)) {
                            $errList = implode("\n", array_slice($import->failures, 0, 5));
                            Notification::make()
                                ->warning()
                                ->title("تم الرفع جزئياً — {$total} سجل")
                                ->body("أخطاء في بعض الصفوف:\n{$errList}")
                                ->persistent()
                                ->send();
                        } else {
                            Notification::make()
                                ->success()
                                ->title('تم الرفع بنجاح ✓')
                                ->body("تم إنشاء {$import->importedCount} سجل جديد وتحديث {$import->updatedCount} سجل.")
                                ->send();
                        }
                    }),

                // 4. تصدير البيانات إلى Excel
                Action::make('export_excel')
                    ->label('تصدير السجلات إلى Excel')
                    ->icon('heroicon-o-table-cells')
                    ->color('success')
                    ->form([
                        Select::make('port_id')
                            ->label('الميناء')
                            ->placeholder('كافة الموانئ')
                            ->options(Port::where('is_active', true)->where(fn ($q) => $q->where('has_monthly_records', true)->orWhereHas('monthlyPortRecords'))->orderBy('sort_order')->pluck('name_ar', 'id')),

                        Select::make('fiscal_year_id')
                            ->label('السنة المالية')
                            ->placeholder('كافة السنوات')
                            ->options(FiscalYear::orderBy('year', 'desc')->pluck('year', 'id')),
                    ])
                    ->modalHeading('تصدير السجلات التشغيلية إلى ملف Excel')
                    ->modalDescription('سيتم إنشاء ملف Excel مطابق للقالب التشغيلي الرسمي لشركة الموانئ العراقية.')
                    ->modalSubmitActionLabel('بدء التصدير والتحميل')
                    ->action(function (array $data) {
                        $params = http_build_query(array_filter([
                            'port_id' => $data['port_id'] ?? null,
                            'fiscal_year_id' => $data['fiscal_year_id'] ?? null,
                        ]));

                        return redirect()->route('admin.reports.monthly-port-records.excel', $params ? "?{$params}" : '');
                    }),

                // 5. تحميل قالب Excel الرسمي
                Action::make('download_template')
                    ->label('تحميل قالب Excel الرسمي')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->action(function () {
                        return Excel::download(new PortRecordTemplateExport, 'قالب_البيانات_التشغيلية.xlsx');
                    }),

                // 6. إصدار مذكرة الطاقة الإنتاجية (نموذج 88)
                Action::make('official_capacity_memo')
                    ->label('مذكرة الطاقة الإنتاجية (نموذج 88)')
                    ->icon('heroicon-o-document-text')
                    ->color('warning')
                    ->form([
                        Select::make('fiscal_year_id')
                            ->label('السنة المالية')
                            ->options(FiscalYear::orderBy('year', 'desc')->pluck('year', 'id'))
                            ->default(FiscalYear::where('is_current', true)->first()?->id),
                        Select::make('month_id')
                            ->label('الشهر')
                            ->options(Month::orderBy('month_number')->pluck('name_ar', 'id'))
                            ->default(Month::where('month_number', 8)->first()?->id),
                        TextInput::make('memo_number')
                            ->label('رقم المذكرة (مطلوب)')
                            ->required()
                            ->default('88'),
                        TextInput::make('memo_date')
                            ->label('تاريخ المذكرة (مطلوب)')
                            ->required()
                            ->default('9 / 9 / '.(FiscalYear::where('is_current', true)->first()?->year ?? date('Y'))),
                    ])
                    ->modalHeading('إصدار مذكرة الطاقة الإنتاجية للموانئ (نموذج م.ت 88)')
                    ->modalDescription('المذكرة موجهة للسيد المدير العام وتتضمن الطاقة الإنتاجية للموانئ الأربعة والشركة ومقارنة بالسنة السابقة.')
                    ->modalSubmitActionLabel('معاينة وطباعة الكتاب')
                    ->action(function (array $data) {
                        $params = http_build_query([
                            'fiscal_year_id' => $data['fiscal_year_id'],
                            'month_id' => $data['month_id'],
                            'memo_number' => $data['memo_number'],
                            'memo_date' => $data['memo_date'],
                        ]);

                        return redirect()->away(route('admin.official-letters.preview', ['type' => 'capacity_memo']).'?'.$params);
                    }),
            ])
            ->label('خيارات وإجراءات السجلات التشغيلية')
            ->icon('heroicon-o-chevron-down')
            ->iconPosition(IconPosition::After)
            ->dropdownWidth(Width::Large)
            ->button()
            ->color('primary'),
        ];
    }
}
