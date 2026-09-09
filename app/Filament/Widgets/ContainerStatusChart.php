<?php

namespace App\Filament\Widgets;

use App\Models\ContainerStatusDetail;
use App\Models\FiscalYear;
use App\Models\Port;
use Illuminate\Support\Facades\Auth;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class ContainerStatusChart extends ApexChartWidget
{
    protected static ?string $chartId = 'containerStatusChart';
    protected static ?string $heading = '📦 موقف الحاويات المتخلفة والخطرة حسب الموانئ';
    protected static ?int $sort = 11;
    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        $user = Auth::user();
        if (! $user) return false;

        return $user->hasRole(['super_admin', 'المدير العام', 'general_manager', 'reviewer', 'مدقق / مراجع', 'operations_manager', 'مسؤول المتابعة المركزية والعمليات'])
            || $user->can('view_any_container::status::record')
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

            $abandoned = ContainerStatusDetail::whereHas('record', function ($q) use ($port, $currentYear) {
                $q->where('port_id', $port->id)
                  ->where('container_type', 'abandoned')
                  ->when($currentYear, fn ($sq) => $sq->where('fiscal_year_id', $currentYear->id));
            })->sum('count');

            $dangerous = ContainerStatusDetail::whereHas('record', function ($q) use ($port, $currentYear) {
                $q->where('port_id', $port->id)
                  ->where('container_type', 'dangerous')
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
                    'name' => 'حاويات متخلفة',
                    'data' => $abandonedData,
                ],
                [
                    'name' => 'حاويات خطرة',
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
            'colors' => ['#f59e0b', '#ef4444'],
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
                'horizontalAlign' => 'center',
                'fontFamily' => 'IBM Plex Sans Arabic, sans-serif',
                'fontWeight' => 600,
            ],
            'grid' => [
                'borderColor' => '#f1f5f9',
            ],
        ];
    }
}
