<?php

namespace App\Filament\Resources\RevenueRecordResource\Pages;

use App\Exports\RevenueRecordTemplateExport;
use App\Filament\Resources\RevenueRecordResource;
use App\Imports\RevenueRecordImport;
use App\Models\FiscalYear;
use App\Models\Month;
use App\Models\RevenueCenter;
use App\Services\SingleRevenueMonthlyImportService;
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
use Maatwebsite\Excel\Facades\Excel;

class ListRevenueRecords extends ListRecords
{
    protected static string $resource = RevenueRecordResource::class;

    protected ?string $heading = 'سجلات الإيراد للمراكز السبعة';

    protected function getHeaderActions(): array
    {
        return [
            // ─── قائمة منسدلة جامعة لكافة إجراءات وخيارات سجلات الإيراد ───
            ActionGroup::make([
                // 1. ميزة الإدخال المباشر (اليدوي)
                CreateAction::make()
                    ->label('إضافة سجل إيراد (إدخال يدوي مباشر)')
                    ->icon('heroicon-o-plus')
                    ->color('primary'),

                // 2. ميزة استيراد إيراد شهر معين من Excel (لمركز محدد أو لكافة المراكز السبعة)
                Action::make('import_single_revenue_month')
                    ->label('استيراد إيراد شهر محدد من Excel')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('success')
                    ->modalHeading('استيراد بيانات الإيراد لشهر معين من Excel')
                    ->modalDescription('اختر الشهر والسنة ومركز الإيراد المستهدف (أو اتركه لكافة المراكز معاً)، ثم ارفع ملف الإكسل ليتم سحب الإيراد الكلي والصافي فورياً.')
                    ->modalSubmitActionLabel('بدء الاستيراد والحفظ')
                    ->modalIcon('heroicon-o-banknotes')
                    ->modalWidth(Width::Large)
                    ->form([
                        Grid::make(3)->schema([
                            Select::make('revenue_center_id')
                                ->label('مركز الإيراد المستهدف')
                                ->placeholder('كافة المراكز السبعة معاً')
                                ->options(RevenueCenter::where('is_active', true)->orderBy('sort_order')->pluck('name_ar', 'id'))
                                ->helperText('اتركه فارغاً لاستيراد كافة المراكز الموجودة بالملف'),

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
                            ->label('ملف Excel الخاص ببيانات الإيراد')
                            ->helperText('يقبل ملفات Excel (.xlsx, .xls, .csv). يتم قراءة الإيراد الكلي والصافي ومطابقة المراكز تلقائياً.')
                            ->acceptedFileTypes([
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                'application/vnd.ms-excel',
                                'text/csv',
                            ])
                            ->required()
                            ->maxSize(51200)
                            ->disk('local')
                            ->directory('imports/single-revenue-monthly')
                            ->preserveFilenames(false),
                    ])
                    ->action(function (array $data) {
                        $path = storage_path('app/private/'.$data['excel_file']);
                        if (! file_exists($path)) {
                            $path = storage_path('app/'.$data['excel_file']);
                        }

                        $service = app(SingleRevenueMonthlyImportService::class);

                        try {
                            $res = $service->import(
                                ! empty($data['revenue_center_id']) ? (int) $data['revenue_center_id'] : null,
                                (int) $data['fiscal_year_id'],
                                (int) $data['month_id'],
                                $path
                            );

                            $formattedGross = number_format($res['total_gross'], 0).' د.ع';
                            $formattedNet   = number_format($res['total_net'], 0).' د.ع';

                            Notification::make()
                                ->success()
                                ->title('تم استيراد وحفظ بيانات الإيراد بنجاح ✓')
                                ->body("المركز: {$res['center_name']} | شهر: {$res['month_name']} {$res['year']}\nتم تحديث ({$res['records_count']}) سجل\nالإيراد الكلي: {$formattedGross}\nالإيراد الصافي: {$formattedNet}")
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

                // 3. رفع ملف البيانات الشامل (عدة مراكز وأشهر)
                Action::make('import_revenue')
                    ->label('استيراد ملف شامل (عدة مراكز وأشهر)')
                    ->icon('heroicon-o-cloud-arrow-up')
                    ->color('info')
                    ->form([
                        FileUpload::make('excel_file')
                            ->label('ملف Excel الشامل (xlsx/xls/csv)')
                            ->helperText('ارفع الملف المعبأ وفق قالب بيانات الإيراد الشامل لعدة مراكز وأشهر')
                            ->acceptedFileTypes([
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                'application/vnd.ms-excel',
                                'text/csv',
                            ])
                            ->required()
                            ->maxSize(102400)
                            ->disk('local')
                            ->directory('imports/revenue-records')
                            ->preserveFilenames(false),
                    ])
                    ->modalHeading('رفع بيانات الإيراد الشاملة للمراكز')
                    ->modalDescription('يمكنك رفع إيرادات مراكز وأشهر متعددة في ملف واحد. سيتم تحديث الموجود أو إنشاء سجل جديد تلقائياً.')
                    ->modalSubmitActionLabel('رفع وحفظ')
                    ->modalIcon('heroicon-o-cloud-arrow-up')
                    ->modalWidth(Width::Large)
                    ->action(function (array $data) {
                        $path = storage_path('app/private/'.$data['excel_file']);
                        if (! file_exists($path)) {
                            $path = storage_path('app/'.$data['excel_file']);
                        }

                        $import = new RevenueRecordImport;

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

                // 4. تصدير بيانات الإيراد إلى Excel
                Action::make('export_excel')
                    ->label('تصدير السجلات إلى Excel')
                    ->icon('heroicon-o-table-cells')
                    ->color('success')
                    ->form([
                        Select::make('revenue_center_id')
                            ->label('مركز الإيراد / التشكيل')
                            ->placeholder('كافة المراكز السبعة')
                            ->options(RevenueCenter::where('is_active', true)->orderBy('sort_order')->pluck('name_ar', 'id')),

                        Select::make('fiscal_year_id')
                            ->label('السنة المالية')
                            ->placeholder('كافة السنوات')
                            ->options(FiscalYear::orderBy('year', 'desc')->pluck('year', 'id')),
                    ])
                    ->modalHeading('تصدير سجلات الإيراد للمراكز السبعة إلى Excel')
                    ->modalDescription('سيتم إنشاء ملف Excel مفصل لكافة إيرادات واستقطاعات المراكز السبعة مع صف المجموع الكلي.')
                    ->modalSubmitActionLabel('بدء التصدير والتحميل')
                    ->action(function (array $data) {
                        $params = http_build_query(array_filter([
                            'revenue_center_id' => $data['revenue_center_id'] ?? null,
                            'fiscal_year_id'    => $data['fiscal_year_id'] ?? null,
                        ]));

                        return redirect()->route('admin.reports.revenue-records.excel', $params ? "?{$params}" : '');
                    }),

                // 5. تحميل قالب Excel الرسمي
                Action::make('download_template')
                    ->label('تحميل قالب Excel الرسمي')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->action(function () {
                        return Excel::download(new RevenueRecordTemplateExport, 'قالب_بيانات_الإيراد.xlsx');
                    }),

                // 6. إصدار مذكرة الإيراد الرسمية (نموذج 87)
                Action::make('official_revenue_memo')
                    ->label('مذكرة الإيراد (نموذج 87)')
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
                            ->default('87'),
                        TextInput::make('memo_date')
                            ->label('تاريخ المذكرة (مطلوب)')
                            ->required()
                            ->default('9 / 9 / '.(FiscalYear::where('is_current', true)->first()?->year ?? date('Y'))),
                    ])
                    ->modalHeading('إصدار مذكرة الإيراد الكلي والصافي (نموذج م.ت 87)')
                    ->modalDescription('المذكرة موجهة للسيد المدير العام وتتضمن الإيراد الكلي والصافي للمراكز السبعة ومقارنة بالسنة السابقة.')
                    ->modalSubmitActionLabel('معاينة وطباعة الكتاب')
                    ->action(function (array $data) {
                        $params = http_build_query([
                            'fiscal_year_id' => $data['fiscal_year_id'],
                            'month_id'       => $data['month_id'],
                            'memo_number'    => $data['memo_number'],
                            'memo_date'      => $data['memo_date'],
                        ]);

                        return redirect()->away(route('admin.official-letters.preview', ['type' => 'revenue_memo']).'?'.$params);
                    }),
            ])
            ->label('خيارات وإجراءات سجلات الإيراد')
            ->icon('heroicon-o-chevron-down')
            ->iconPosition(IconPosition::After)
            ->dropdownWidth(Width::Large)
            ->button()
            ->color('primary'),
        ];
    }
}
