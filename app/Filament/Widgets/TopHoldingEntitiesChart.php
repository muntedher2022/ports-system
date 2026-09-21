<?php

namespace App\Filament\Widgets;

use App\Models\ContainerEntity;
use App\Models\ContainerStatusDetail;
use App\Models\FiscalYear;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class TopHoldingEntitiesChart extends ApexChartWidget
{
    protected static ?string $chartId = 'topHoldingEntitiesChart';
    protected static ?string $heading = '🏛️ أعلى 8 جهات ووزارات متراكمة لديها الحاويات في الموانئ';
    protected static ?int $sort = 15;
    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        $user = Auth::user();
        if (! $user) return false;

        return $user->hasRole(['super_admin', 'المدير العام', 'general_manager', 'reviewer', 'مدقق / مراجع', 'operations_manager', 'مسؤول المتابعة المركزية والعمليات'])
            || ! ($user->isPortRestricted() && $user->port_id);
    }

    protected function getOptions(): array
    {
        $currentYear = FiscalYear::where('is_current', true)->first()
            ?? FiscalYear::orderBy('year', 'desc')->first();

        // جلب أعلى 8 جهات في رصيد الحاويات
        $topEntities = ContainerStatusDetail::select('container_entity_id', DB::raw('SUM(count) as total_count'))
            ->whereHas('record', function ($q) use ($currentYear) {
                $q->when($currentYear, fn ($sq) => $sq->where('fiscal_year_id', $currentYear->id));
            })
            ->groupBy('container_entity_id')
            ->orderByDesc('total_count')
            ->limit(8)
            ->with('entity')
            ->get();

        $categories = [];
        $data = [];

        foreach ($topEntities as $row) {
            $name = $row->entity?->name_ar ?? 'جهة غير محددة';
            // تقصير الاسم إذا كان طويلاً
            $categories[] = mb_strlen($name) > 35 ? mb_substr($name, 0, 35) . '...' : $name;
            $data[] = (int) $row->total_count;
        }

        // إذا كانت البيانات فارغة
        if (empty($categories)) {
            $categories = ['لا توجد بيانات مسجلة'];
            $data = [0];
        }

        return [
            'chart' => [
                'type' => 'bar',
                'height' => 320,
                'fontFamily' => 'IBM Plex Sans Arabic, sans-serif',
                'toolbar' => ['show' => false],
            ],
            'series' => [
                [
                    'name' => 'عدد الحاويات المتراكمة',
                    'data' => $data,
                ],
            ],
            'plotOptions' => [
                'bar' => [
                    'horizontal' => true,
                    'borderRadius' => 6,
                    'barHeight' => '55%',
                    'dataLabels' => [
                        'position' => 'top',
                    ],
                ],
            ],
            'dataLabels' => [
                'enabled' => true,
                'offsetX' => -10,
                'style' => [
                    'fontSize' => '11px',
                    'fontFamily' => 'IBM Plex Sans Arabic, sans-serif',
                    'fontWeight' => 700,
                    'colors' => ['#ffffff'],
                ],
                'formatter' => 'function(val) { return val > 0 ? val.toLocaleString() : ""; }',
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
                        'fontWeight' => 600,
                    ],
                ],
            ],
            'colors' => ['#4f46e5'],
            'grid' => [
                'borderColor' => '#f1f5f9',
                'strokeDashArray' => 4,
            ],
            'tooltip' => [
                'y' => [
                    'formatter' => 'function(val) { return val.toLocaleString() + " حاوية"; }',
                ],
            ],
        ];
    }
}
