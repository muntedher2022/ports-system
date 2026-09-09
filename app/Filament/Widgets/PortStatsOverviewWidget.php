<?php

namespace App\Filament\Widgets;

use App\Models\FiscalYear;
use App\Models\MonthlyPortRecord;
use App\Models\RevenueRecord;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class PortStatsOverviewWidget extends Widget
{
    protected static ?string $heading = '📊 مؤشرات وإحصائيات الطاقة التشغيلية والإيراد العام';
    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 'full';
    protected string $view = 'filament.widgets.port-stats-overview-widget';

    public function getHeading(): ?string
    {
        return static::$heading;
    }

    public function getCachedStats(): array
    {
        $user = Auth::user();
        $portId = ($user?->isPortRestricted() && $user?->port_id) ? $user->port_id : null;

        $currentYear = FiscalYear::where('is_current', true)->first()
            ?? FiscalYear::orderBy('year', 'desc')->first();

        $prevYear = FiscalYear::where('year', ($currentYear?->year ?? 2026) - 1)->first()
            ?? FiscalYear::where('id', '!=', $currentYear?->id)->orderBy('year', 'desc')->first();

        $currRecords = MonthlyPortRecord::query()
            ->when($currentYear, fn ($q) => $q->where('fiscal_year_id', $currentYear->id))
            ->when($portId, fn ($q) => $q->where('port_id', $portId))
            ->get();

        $activeMonths = $currRecords->pluck('month_id')->unique()->toArray();
        $activePorts = $currRecords->pluck('port_id')->unique()->toArray();

        $prevRecords = MonthlyPortRecord::query()
            ->when($prevYear, fn ($q) => $q->where('fiscal_year_id', $prevYear->id))
            ->when($portId, fn ($q) => $q->where('port_id', $portId))
            ->whereIn('month_id', $activeMonths)
            ->whereIn('port_id', $activePorts)
            ->get();

        // 1. السفن
        $currShips = (int) $currRecords->sum('total_ships');
        $prevShips = (int) $prevRecords->sum('total_ships');
        $shipsDiff = $prevShips > 0 ? round((($currShips - $prevShips) / $prevShips) * 100, 1) : 0;

        // 2. الحاويات TEU
        $currTeu = (int) ($currRecords->sum('imported_teu') + $currRecords->sum('exported_teu'));
        $prevTeu = (int) ($prevRecords->sum('imported_teu') + $prevRecords->sum('exported_teu'));
        $teuDiff = $prevTeu > 0 ? round((($currTeu - $prevTeu) / $prevTeu) * 100, 1) : 0;

        // 3. البضائع والنفطية (طن)
        $currTons = (float) (
            $currRecords->sum('imported_general_cargo_tons') +
            $currRecords->sum('exported_general_cargo_tons') +
            $currRecords->sum('oil_total_tons')
        );
        $prevTons = (float) (
            $prevRecords->sum('imported_general_cargo_tons') +
            $prevRecords->sum('exported_general_cargo_tons') +
            $prevRecords->sum('oil_total_tons')
        );
        $tonsDiff = $prevTons > 0 ? round((($currTons - $prevTons) / $prevTons) * 100, 1) : 0;

        // 4. الإيراد الكلي
        if ($portId) {
            $currRev = (float) $currRecords->sum('total_revenue');
            $prevRev = (float) $prevRecords->sum('total_revenue');
        } else {
            $currRev = (float) RevenueRecord::when($currentYear, fn ($q) => $q->where('fiscal_year_id', $currentYear->id))
                ->whereIn('month_id', $activeMonths)
                ->sum('gross_revenue');
            $prevRev = (float) RevenueRecord::when($prevYear, fn ($q) => $q->where('fiscal_year_id', $prevYear->id))
                ->whereIn('month_id', $activeMonths)
                ->sum('gross_revenue');
        }
        $revDiff = $prevRev > 0 ? round((($currRev - $prevRev) / $prevRev) * 100, 1) : 0;

        $prevYearLabel = $prevYear?->year ?? 2025;

        return [
            [
                'label'       => 'حركة السفن الواصلة',
                'value'       => number_format($currShips) . ' سفينة',
                'diff'        => $shipsDiff,
                'prev_year'   => $prevYearLabel,
                'color_class' => 'card-blue',
                'icon_bg'     => 'bg-blue-100 text-blue-700',
                'icon_svg'    => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"></path></svg>',
            ],
            [
                'label'       => 'الحاويات المكافئة (TEU)',
                'value'       => number_format($currTeu) . ' TEU',
                'diff'        => $teuDiff,
                'prev_year'   => $prevYearLabel,
                'color_class' => 'card-cyan',
                'icon_bg'     => 'bg-cyan-100 text-cyan-700',
                'icon_svg'    => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>',
            ],
            [
                'label'       => 'إجمالي المشتقات النفطية',
                'value'       => number_format($currTons) . ' طن',
                'diff'        => $tonsDiff,
                'prev_year'   => $prevYearLabel,
                'color_class' => 'card-emerald',
                'icon_bg'     => 'bg-emerald-100 text-emerald-700',
                'icon_svg'    => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>',
            ],
            [
                'label'       => 'الإيراد الكلي المتحقق',
                'value'       => number_format($currRev) . ' د.ع',
                'diff'        => $revDiff,
                'prev_year'   => $prevYearLabel,
                'color_class' => 'card-amber',
                'icon_bg'     => 'bg-amber-100 text-amber-700',
                'icon_svg'    => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>',
            ],
        ];
    }
}
