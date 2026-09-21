<?php

namespace App\Filament\Widgets;

use App\Models\CargoStatusDetail;
use App\Models\FiscalYear;
use App\Models\Port;
use Illuminate\Support\Facades\Auth;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class CargoStatusChart extends ApexChartWidget
{
    protected static ?string $chartId = 'cargoStatusChart';
    protected static ?string $heading = '📦 موقف المواد والبضائع المتخلفة والخطرة حسب الموانئ';
    protected static ?int $sort = 8;
    protected int | string | array $columnSpan = 1;

    public static function canView(): bool
    {
        $user = Auth::user();
        if (! $user) return false;

        return $user->hasRole(['super_admin', 'المدير العام', 'general_manager', 'reviewer', 'مدقق / مراجع', 'operations_manager', 'مسؤول المتابعة المركزية والعمليات'])
            || $user->can('view_any_cargo::status::record')
            || ! ($user->isPortRestricted() && $user->port_id);
    }

    protected function getOptions(): array
    {
        $currentYear = FiscalYear::where('is_current', true)->first()
            ?? FiscalYear::orderBy('year', 'desc')->first();

        $ports = Port::where('is_active', true)->orderBy('id')->get();

        $categories = [];
        $abandonedData = [];
        $dangerousData = [];

        foreach ($ports as $port) {
            $categories[] = $port->name_ar;

            $abandoned = CargoStatusDetail::whereHas('record', function ($q) use ($port, $currentYear) {
                $q->where('port_id', $port->id)
                  ->where('cargo_type', 'abandoned')
                  ->when($currentYear, fn ($sq) => $sq->where('fiscal_year_id', $currentYear->id));
            })->sum('count');

            $dangerous = CargoStatusDetail::whereHas('record', function ($q) use ($port, $currentYear) {
                $q->where('port_id', $port->id)
                  ->where('cargo_type', 'dangerous')
                  ->when($currentYear, fn ($sq) => $sq->where('fiscal_year_id', $currentYear->id));
            })->sum('count');

            $abandonedData[] = (int) $abandoned;
            $dangerousData[] = (int) $dangerous;
        }

        return [
            'chart' => [
                'type' => 'bar',
                'height' => 310,
                'fontFamily' => 'IBM Plex Sans Arabic, sans-serif',
                'toolbar' => ['show' => false],
            ],
            'series' => [
                [
                    'name' => 'مواد وبضائع متخلفة',
                    'data' => $abandonedData,
                ],
                [
                    'name' => 'مواد وبضائع خطرة',
                    'data' => $dangerousData,
                ],
            ],
            'xaxis' => [
                'categories' => $categories,
                'labels' => [
                    'style' => [
                        'fontFamily' => 'IBM Plex Sans Arabic, sans-serif',
                        'fontWeight' => 600,
                    ],
                ],
            ],
            'yaxis' => [
                'labels' => [
                    'style' => [
                        'fontFamily' => 'IBM Plex Sans Arabic, sans-serif',
                    ],
                ],
            ],
            'colors' => ['#f59e0b', '#f43f5e'],
            'plotOptions' => [
                'bar' => [
                    'borderRadius' => 6,
                    'columnWidth' => '45%',
                    'dataLabels' => [
                        'position' => 'top',
                    ],
                ],
            ],
            'dataLabels' => [
                'enabled' => false,
            ],
            'legend' => [
                'position' => 'top',
                'horizontalAlign' => 'right',
                'fontFamily' => 'IBM Plex Sans Arabic, sans-serif',
                'fontWeight' => 600,
                'labels' => [
                    'colors' => '#334155',
                ],
            ],
            'grid' => [
                'borderColor' => '#f1f5f9',
                'strokeDashArray' => 4,
            ],
            'tooltip' => [
                'y' => [
                    'formatter' => 'function(val) { return val.toLocaleString() + " طرد/مادة"; }',
                ],
            ],
        ];
    }
}
