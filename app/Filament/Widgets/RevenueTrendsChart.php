<?php

namespace App\Filament\Widgets;

use App\Models\FiscalYear;
use App\Models\RevenueRecord;
use Illuminate\Support\Facades\Auth;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class RevenueTrendsChart extends ApexChartWidget
{
    protected static ?string $chartId = 'revenueTrendsChart';
    protected static ?string $heading = '💰 حركة الإيرادات المالية الشهرية (الإيراد الكلي مقابل الصافي)';
    protected static ?int $sort = 4;

    public static function canView(): bool
    {
        // يظهر للإدارة العامة والمالية والمتابعة فقط
        $user = Auth::user();
        return ! ($user?->isPortRestricted() && $user?->port_id);
    }

    protected function getOptions(): array
    {
        $currentYear = FiscalYear::where('is_current', true)->first()
            ?? FiscalYear::orderBy('year', 'desc')->first();

        $grossMonthly = [];
        $netMonthly = [];

        for ($m = 1; $m <= 12; $m++) {
            $records = RevenueRecord::query()
                ->when($currentYear, fn ($q) => $q->where('fiscal_year_id', $currentYear->id))
                ->where('month_id', $m)
                ->get();

            $grossSum = (float) $records->sum('gross_revenue');
            $netSum = (float) $records->sum('net_revenue');

            $grossMonthly[] = round($grossSum);
            $netMonthly[] = round($netSum);
        }

        $yearLabel = (string) ($currentYear?->year ?? '2026');

        return [
            'chart' => [
                'type' => 'bar',
                'height' => 310,
                'fontFamily' => 'IBM Plex Sans Arabic, sans-serif',
                'toolbar' => [
                    'show' => false,
                ],
            ],
            'series' => [
                [
                    'name' => 'الإيراد الكلي (د.ع)',
                    'data' => $grossMonthly,
                ],
                [
                    'name' => 'الإيراد الصافي (د.ع)',
                    'data' => $netMonthly,
                ],
            ],
            'xaxis' => [
                'categories' => ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12'],
                'title' => [
                    'text' => "الأشهر لسنة {$yearLabel}",
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
            ],
            'colors' => ['#0284c7', '#10b981'],
            'plotOptions' => [
                'bar' => [
                    'horizontal' => false,
                    'columnWidth' => '55%',
                    'borderRadius' => 4,
                ],
            ],
            'dataLabels' => [
                'enabled' => false,
            ],
            'legend' => [
                'position' => 'top',
                'horizontalAlign' => 'center',
                'fontFamily' => 'inherit',
            ],
            'tooltip' => [
                'y' => [
                    'formatter' => 'function (val) { return Number(val).toLocaleString() + " د.ع"; }',
                ],
            ],
        ];
    }
}
