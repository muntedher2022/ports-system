<?php

namespace App\Filament\Resources\MonthlyPortRecordResource\Pages;

use App\Filament\Resources\MonthlyPortRecordResource;
use App\Imports\PortRecordImport;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Maatwebsite\Excel\Facades\Excel;

class ListMonthlyPortRecords extends ListRecords
{
    protected static string $resource = MonthlyPortRecordResource::class;
    protected ?string $heading = 'السجلات التشغيلية للموانئ';

    protected function getHeaderActions(): array
    {
        return [
            // ─── زر تصدير البيانات إلى Excel ───
            Action::make('export_excel')
                ->label('تصدير إلى Excel')
                ->icon('heroicon-o-table-cells')
                ->color('success')
                ->form([
                    \Filament\Forms\Components\Select::make('port_id')
                        ->label('الميناء')
                        ->placeholder('كافة الموانئ')
                        ->options(\App\Models\Port::where('is_active', true)->where(fn($q) => $q->where('has_monthly_records', true)->orWhereHas('monthlyPortRecords'))->orderBy('sort_order')->pluck('name_ar', 'id')),

                    \Filament\Forms\Components\Select::make('fiscal_year_id')
                        ->label('السنة المالية')
                        ->placeholder('كافة السنوات')
                        ->options(\App\Models\FiscalYear::orderBy('year', 'desc')->pluck('year', 'id')),
                ])
                ->modalHeading('تصدير السجلات التشغيلية إلى ملف Excel')
                ->modalDescription('سيتم إنشاء ملف Excel مطابق للقالب التشغيلي الرسمي لشركة الموانئ العراقية.')
                ->modalSubmitActionLabel('بدء التصدير والتحميل')
                ->action(function (array $data) {
                    $params = http_build_query(array_filter([
                        'port_id'        => $data['port_id'] ?? null,
                        'fiscal_year_id' => $data['fiscal_year_id'] ?? null,
                    ]));

                    return redirect()->route('admin.reports.monthly-port-records.excel', $params ? "?{$params}" : '');
                }),

            // ─── زر تحميل قالب Excel ───
            Action::make('download_template')
                ->label('تحميل قالب Excel')
                ->icon('heroicon-o-document-text')
                ->color('info')
                ->action(function () {
                    return Excel::download(new \App\Exports\PortRecordTemplateExport(), 'قالب_البيانات_التشغيلية.xlsx');
                }),

            // ─── زر رفع ملف البيانات ───
            Action::make('import_records')
                ->label('رفع ملف البيانات')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                ->form([
                    FileUpload::make('excel_file')
                        ->label('ملف Excel (xlsx/xls/csv)')
                        ->helperText('ارفع الملف المعبأ وفق القالب المرفوع مسبقاً')
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-excel',
                            'text/csv',
                        ])
                        ->required()
                        ->disk('local')
                        ->directory('imports/port-records')
                        ->preserveFilenames(false),
                ])
                ->modalHeading('رفع بيانات السجلات التشغيلية')
                ->modalDescription('يمكنك رفع بيانات أشهر متعددة وموانئ متعددة في ملف واحد. سيتم إنشاء سجلات جديدة أو تحديث الموجودة تلقائياً.')
                ->modalSubmitActionLabel('رفع وحفظ')
                ->modalIcon('heroicon-o-cloud-arrow-up')
                ->modalWidth('lg')
                ->action(function (array $data) {
                    $path = storage_path('app/private/' . $data['excel_file']);

                    $import = new PortRecordImport();

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

                    if (!empty($import->failures)) {
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

            CreateAction::make()->label('إضافة سجل تشغيلي شهري'),
        ];
    }
}
