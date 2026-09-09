<?php

namespace App\Filament\Widgets;

use App\Models\FiscalYear;
use App\Models\MonthlyPortRecord;
use Illuminate\Support\Facades\Auth;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class MonthlyPerformanceChart extends ApexChartWidget
{
    protected static ?string $chartId = 'monthlyPerformanceChart';
    protected static ?string $heading = '📈 مقارنة الأداء التشغيلي للبضائع والنفطية (طن) شهرياً بين عامين';
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';

    protected function getOptions(): array
    {
        $user = Auth::user();
        $portId = ($user?->isPortRestricted() && $user?->port_id) ? $user->port_id : null;

        $currentYear = FiscalYear::where('is_current', true)->first()
            ?? FiscalYear::orderBy('year', 'desc')->first();

        $prevYear = FiscalYear::where('year', ($currentYear?->year ?? 2026) - 1)->first()
            ?? FiscalYear::where('id', '!=', $currentYear?->id)->orderBy('year', 'desc')->first();

        $currYearLabel = (string) ($currentYear?->year ?? '2026');
        $prevYearLabel = (string) ($prevYear?->year ?? '2025');

        $currMonthlyTons = [];
        $prevMonthlyTons = [];

        for ($m = 1; $m <= 12; $m++) {
            $cRecs = MonthlyPortRecord::query()
                ->when($currentYear, fn ($q) => $q->where('fiscal_year_id', $currentYear->id))
                ->when($portId, fn ($q) => $q->where('port_id', $portId))
                ->where('month_id', $m)
                ->get();

            $pRecs = MonthlyPortRecord::query()
                ->when($prevYear, fn ($q) => $q->where('fiscal_year_id', $prevYear->id))
                ->when($portId, fn ($q) => $q->where('port_id', $portId))
                ->where('month_id', $m)
                ->get();

            $cTotal = (float) (
                $cRecs->sum('imported_general_cargo_tons') +
                $cRecs->sum('exported_general_cargo_tons') +
                $cRecs->sum('oil_total_tons')
            );

            $pTotal = (float) (
                $pRecs->sum('imported_general_cargo_tons') +
                $pRecs->sum('exported_general_cargo_tons') +
                $pRecs->sum('oil_total_tons')
            );

            $currMonthlyTons[] = round($cTotal);
            $prevMonthlyTons[] = round($pTotal);
        }

        return [
            'chart' => [
                'type' => 'area',
                'height' => 320,
                'fontFamily' => 'IBM Plex Sans Arabic, sans-serif',
                'toolbar' => [
                    'show' => true,
                ],
            ],
            'series' => [
                [
                    'name' => "عام {$prevYearLabel} (السابق)",
                    'data' => $prevMonthlyTons,
                ],
                [
                    'name' => "عام {$currYearLabel} (الحالي)",
                    'data' => $currMonthlyTons,
                ],
            ],
            'xaxis' => [
                'categories' => ['شهر 1', 'شهر 2', 'شهر 3', 'شهر 4', 'شهر 5', 'شهر 6', 'شهر 7', 'شهر 8', 'شهر 9', 'شهر 10', 'شهر 11', 'شهر 12'],
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
            ],
            'yaxis' => [
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
            ],
            'colors' => ['#94a3b8', '#2563eb'],
            'stroke' => [
                'curve' => 'smooth',
                'width' => [2, 3],
            ],
            'fill' => [
                'type' => 'gradient',
                'gradient' => [
                    'shadeIntensity' => 1,
                    'opacityFrom' => 0.45,
                    'opacityTo' => 0.05,
                    'stops' => [20, 100],
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
                'theme' => 'light',
                'y' => [
                    'formatter' => 'function (val) { return Number(val).toLocaleString() + " طن"; }',
                ],
            ],
        ];
    }
}
