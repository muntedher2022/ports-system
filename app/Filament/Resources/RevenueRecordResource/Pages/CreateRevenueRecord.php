<?php

namespace App\Filament\Resources\RevenueRecordResource\Pages;

use App\Filament\Resources\RevenueRecordResource;
use App\Models\FiscalYear;
use App\Models\Month;
use App\Models\RevenueCenter;
use App\Models\RevenueRecord;
use App\Services\SingleRevenueMonthlyImportService;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Components\Grid;
use Filament\Support\Enums\Width;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CreateRevenueRecord extends CreateRecord
{
    protected static string $resource = RevenueRecordResource::class;

    protected ?string $heading = 'إدخال إيرادات المراكز السبعة لشهر كامل';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('import_single_revenue_month')
                ->label('استيراد بيانات هذا الشهر من Excel')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                ->modalHeading('استيراد بيانات الإيراد من ملف Excel')
                ->modalDescription('بدلاً من الإدخال اليدوي للمراكز السبعة، يمكنك رفع ملف الإكسل ليتم سحب الإيراد الكلي والصافي لكافة المراكز فورياً.')
                ->modalSubmitActionLabel('بدء الاستيراد والحفظ')
                ->modalIcon('heroicon-o-banknotes')
                ->modalWidth(Width::Large)
                ->form([
                    Grid::make(3)->schema([
                        Select::make('revenue_center_id')
                            ->label('مركز الإيراد المستهدف')
                            ->placeholder('كافة المراكز السبعة معاً')
                            ->options(RevenueCenter::where('is_active', true)->orderBy('sort_order')->pluck('name_ar', 'id'))
                            ->helperText('اتركه فارغاً لاستيراد كافة المراكز'),

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

                        return redirect()->to(RevenueRecordResource::getUrl('index'));
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->danger()
                            ->title('خطأ أثناء استيراد الملف')
                            ->body($e->getMessage())
                            ->persistent()
                            ->send();
                    }
                }),
        ];
    }

    protected function handleRecordCreation(array $data): Model
    {
        $fiscalYearId = $data['fiscal_year_id'];
        $monthId = $data['month_id'];
        $status = $data['status'] ?? 'approved';
        $monthlyNet = (float) ($data['monthly_net_revenue'] ?? 0);
        $reopenReason = $data['reopen_reason'] ?? null;
        $userId = Auth::id();

        $centers = RevenueCenter::where('is_active', true)->orderBy('sort_order')->get();
        $totalGross = 0;

        foreach ($centers as $center) {
            $totalGross += (float) ($data["center_{$center->id}"] ?? 0);
        }

        $netRatio = $totalGross > 0 ? ($monthlyNet / $totalGross) : 0;
        $accumulatedNet = 0;
        $lastRecord = null;

        foreach ($centers as $idx => $center) {
            $gross = (float) ($data["center_{$center->id}"] ?? 0);
            $isLast = ($idx === count($centers) - 1);

            if ($isLast) {
                $net = round($monthlyNet - $accumulatedNet, 3);
            } else {
                $net = round($gross * $netRatio, 3);
            }

            $existing = RevenueRecord::withTrashed()->where([
                'revenue_center_id' => $center->id,
                'fiscal_year_id' => $fiscalYearId,
                'month_id' => $monthId,
            ])->first();

            if ($existing) {
                if ($existing->trashed()) {
                    $existing->restore();
                }
                $existing->update([
                    'gross_revenue' => $gross,
                    'net_revenue' => $net,
                    'status' => $status,
                    'reopen_reason' => $reopenReason,
                    'created_by' => $userId,
                ]);
                $lastRecord = $existing;
            } else {
                $lastRecord = RevenueRecord::create([
                    'revenue_center_id' => $center->id,
                    'fiscal_year_id' => $fiscalYearId,
                    'month_id' => $monthId,
                    'gross_revenue' => $gross,
                    'net_revenue' => $net,
                    'status' => $status,
                    'reopen_reason' => $reopenReason,
                    'created_by' => $userId,
                ]);
            }
        }

        return $lastRecord ?? new RevenueRecord;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
