<?php

namespace App\Filament\Resources\RevenueRecordResource\Pages;

use App\Filament\Resources\RevenueRecordResource;
use App\Imports\RevenueRecordImport;
use App\Models\FiscalYear;
use App\Models\Month;
use App\Models\RevenueCenter;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Maatwebsite\Excel\Facades\Excel;

class ListRevenueRecords extends ListRecords
{
    protected static string $resource = RevenueRecordResource::class;

    protected ?string $heading = 'سجلات الإيراد للمراكز السبعة';

    protected function getHeaderActions(): array
    {
        return [
            // ─── زر إصدار مذكرة الإيراد الرسمية (نموذج 87) ───
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
                        'month_id' => $data['month_id'],
                        'memo_number' => $data['memo_number'],
                        'memo_date' => $data['memo_date'],
                    ]);

                    return redirect()->away(route('admin.official-letters.preview', ['type' => 'revenue_memo']).'?'.$params);
                }),

            // ─── زر تصدير بيانات الإيراد إلى Excel ───
            Action::make('export_excel')
                ->label('تصدير إلى Excel')
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
                        'fiscal_year_id' => $data['fiscal_year_id'] ?? null,
                    ]));

                    return redirect()->route('admin.reports.revenue-records.excel', $params ? "?{$params}" : '');
                }),

            // ─── زر تحميل قالب Excel ───
            Action::make('download_template')
                ->label('تحميل قالب Excel')
                ->icon('heroicon-o-document-text')
                ->color('info')
                ->url(asset('templates/revenue_records_template.xlsx'))
                ->extraAttributes([
                    'download' => 'قالب_بيانات_الإيراد.xlsx',
                ]),

            // ─── زر رفع ملف البيانات ───
            Action::make('import_revenue')
                ->label('رفع ملف الإيراد')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                ->form([
                    FileUpload::make('excel_file')
                        ->label('ملف Excel (xlsx/xls/csv)')
                        ->helperText('ارفع الملف المعبأ وفق قالب بيانات الإيراد')
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
                ->modalHeading('رفع بيانات الإيراد للمراكز')
                ->modalDescription('يمكنك رفع إيرادات مراكز وأشهر متعددة في ملف واحد. سيتم تحديث الموجود أو إنشاء سجل جديد تلقائياً.')
                ->modalSubmitActionLabel('رفع وحفظ')
                ->modalIcon('heroicon-o-cloud-arrow-up')
                ->modalWidth('lg')
                ->action(function (array $data) {
                    $path = storage_path('app/private/'.$data['excel_file']);

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

            CreateAction::make()->label('إضافة سجل إيراد جديد'),
        ];
    }
}
