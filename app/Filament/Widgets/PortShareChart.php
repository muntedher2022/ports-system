<?php

namespace App\Filament\Widgets;

use App\Models\FiscalYear;
use App\Models\MonthlyPortRecord;
use App\Models\Port;
use Illuminate\Support\Facades\Auth;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class PortShareChart extends ApexChartWidget
{
    protected static ?string $chartId = 'portShareChart';
    protected static ?string $heading = '📊 نسبة مساهمة الموانئ في إجمالي البضائع والنشاط';
    protected static ?int $sort = 3;

    public static function canView(): bool
    {
        // يظهر للمدير العام ومسؤول العمليات فقط (لا حاجة له للمستخدم المقيد بميناء واحد)
        $user = Auth::user();
        return ! ($user?->isPortRestricted() && $user?->port_id);
    }

    protected function getOptions(): array
    {
        $currentYear = FiscalYear::where('is_current', true)->first()
            ?? FiscalYear::orderBy('year', 'desc')->first();

        $ports = Port::where('is_active', true)->orderBy('id')->get();

        $labels = [];
        $series = [];

        foreach ($ports as $port) {
            $records = MonthlyPortRecord::query()
                ->when($currentYear, fn ($q) => $q->where('fiscal_year_id', $currentYear->id))
                ->where('port_id', $port->id)
                ->get();

            $totalTons = (float) (
                $records->sum('imported_general_cargo_tons') +
                $records->sum('exported_general_cargo_tons') +
                $records->sum('oil_total_tons')
            );

            $labels[] = $port->name_ar;
            $series[] = round($totalTons);
        }

        // إذا كانت جميعها أصفار، نضع قيماً افتراضية للعرض
        if (array_sum($series) == 0) {
            $series = [1, 1, 1, 1];
        }

        return [
            'chart' => [
                'type' => 'donut',
                'height' => 310,
                'fontFamily' => 'IBM Plex Sans Arabic, sans-serif',
            ],
            'series' => $series,
            'labels' => $labels,
            'colors' => ['#2563eb', '#0284c7', '#0d9488', '#f59e0b'],
            'legend' => [
                'position' => 'bottom',
                'fontFamily' => 'inherit',
            ],
            'plotOptions' => [
                'pie' => [
                    'donut' => [
                        'size' => '65%',
                        'labels' => [
                            'show' => true,
                            'name' => [
                                'show' => true,
                                'fontFamily' => 'inherit',
                            ],
                            'value' => [
                                'show' => true,
                                'fontFamily' => 'inherit',
                            ],
                            'total' => [
                                'show' => true,
                                'label' => 'إجمالي الموانئ',
                                'fontFamily' => 'inherit',
                            ],
                        ],
                    ],
                ],
            ],
            'dataLabels' => [
                'enabled' => true,
            ],
            'stroke' => [
                'width' => 2,
            ],
        ];
    }
}
