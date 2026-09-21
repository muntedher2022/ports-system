<?php

namespace App\Filament\Widgets;

use App\Models\CargoStatusDetail;
use App\Models\ContainerStatusDetail;
use App\Models\FiscalYear;
use App\Models\MonthlyPortRecord;
use App\Models\Port;
use App\Models\RevenueRecord;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MaritimeIntelligenceAgencyWidget extends Widget
{
    protected static ?string $heading = '🌐 وكالة التحليل واستخبارات العمليات المينائية (Maritime Intelligence & Analytics)';
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';
    protected string $view = 'filament.widgets.maritime-intelligence-agency-widget';

    public function getHeading(): ?string
    {
        return static::$heading;
    }

    public static function canView(): bool
    {
        $user = Auth::user();
        if (! $user) return false;

        return $user->hasRole(['super_admin', 'المدير العام', 'general_manager', 'reviewer', 'مدقق / مراجع', 'operations_manager', 'مسؤول المتابعة المركزية والعمليات'])
            || ! ($user->isPortRestricted() && $user->port_id);
    }

    public function getAgencyMetrics(): array
    {
        $user = Auth::user();
        $portId = ($user?->isPortRestricted() && $user?->port_id) ? $user->port_id : null;

        $currentYear = FiscalYear::where('is_current', true)->first()
            ?? FiscalYear::orderBy('year', 'desc')->first();

        $prevYear = FiscalYear::where('year', ($currentYear?->year ?? 2026) - 1)->first()
            ?? FiscalYear::where('id', '!=', $currentYear?->id)->orderBy('year', 'desc')->first();

        // 1. السجلات التشغيلية الحالية والسابقة
        $currRecords = MonthlyPortRecord::query()
            ->when($currentYear, fn ($q) => $q->where('fiscal_year_id', $currentYear->id))
            ->when($portId, fn ($q) => $q->where('port_id', $portId))
            ->get();

        $activeMonths = $currRecords->pluck('month_id')->unique()->filter()->values()->toArray();
        $monthsCount = count($activeMonths) ?: 1;

        $prevRecords = MonthlyPortRecord::query()
            ->when($prevYear, fn ($q) => $q->where('fiscal_year_id', $prevYear->id))
            ->when($portId, fn ($q) => $q->where('port_id', $portId))
            ->whereIn('month_id', $activeMonths)
            ->get();

        // 2. إجمالي الحمولات والسفن
        $currShips = (int) $currRecords->sum('total_ships');
        $prevShips = (int) $prevRecords->sum('total_ships');

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

        // 3. الإيراد الكلي
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

        // الحاويات TEU
        $currImpTeu = (int) $currRecords->sum('imported_teu');
        $currExpTeu = (int) $currRecords->sum('exported_teu');
        $currTotalTeu = $currImpTeu + $currExpTeu;

        // ─── المؤشرات الاستخباراتية التحليلية المتقدمة ───

        // أ) مؤشر العائد المالي للطن (Revenue Density / Yield per Metric Ton)
        $revPerTon = $currTons > 0 ? round($currRev / $currTons, 0) : 0;
        $prevRevPerTon = $prevTons > 0 ? round($prevRev / $prevTons, 0) : 0;
        $revPerTonDiff = $prevRevPerTon > 0 ? round((($revPerTon - $prevRevPerTon) / $prevRevPerTon) * 100, 1) : 0;

        // ب) معدل حمولة وإنتاجية السفينة (Ship Productivity Intensity)
        $payloadPerShip = $currShips > 0 ? round($currTons / $currShips, 0) : 0;
        $prevPayloadPerShip = $prevShips > 0 ? round($prevTons / $prevShips, 0) : 0;
        $payloadDiff = $prevPayloadPerShip > 0 ? round((($payloadPerShip - $prevPayloadPerShip) / $prevPayloadPerShip) * 100, 1) : 0;

        // ج) نسبة التعادل التجاري للحاويات (TEU Export-to-Import Velocity)
        $teuExportRatio = $currImpTeu > 0 ? round(($currExpTeu / $currImpTeu) * 100, 1) : 0;

        // د) التنبؤ التراكمي لنهاية العام (Year-End Run-Rate Forecast)
        $monthlyAvgTons = $currTons / $monthsCount;
        $projectedAnnualTons = round($monthlyAvgTons * 12, 0);

        $monthlyAvgRev = $currRev / $monthsCount;
        $projectedAnnualRev = round($monthlyAvgRev * 12, 0);

        // هـ) الموانئ الرائدة حسب التخصص (Sector Dominance Hubs)
        $ports = Port::where('is_active', true)->get();
        $topContainerHub = ['name' => '—', 'val' => 0];
        $topCargoHub     = ['name' => '—', 'val' => 0];
        $topOilHub       = ['name' => '—', 'val' => 0];
        $topRevenueHub   = ['name' => '—', 'val' => 0];

        foreach ($ports as $p) {
            $pRecs = $currRecords->where('port_id', $p->id);
            $pTeu = $pRecs->sum('imported_teu') + $pRecs->sum('exported_teu');
            $pGenCargo = $pRecs->sum('imported_general_cargo_tons') + $pRecs->sum('exported_general_cargo_tons');
            $pOil = $pRecs->sum('oil_total_tons');
            $pRev = $pRecs->sum('total_revenue');

            if ($pTeu > $topContainerHub['val']) {
                $topContainerHub = ['name' => $p->name_ar, 'val' => $pTeu];
            }
            if ($pGenCargo > $topCargoHub['val']) {
                $topCargoHub = ['name' => $p->name_ar, 'val' => $pGenCargo];
            }
            if ($pOil > $topOilHub['val']) {
                $topOilHub = ['name' => $p->name_ar, 'val' => $pOil];
            }
            if ($pRev > $topRevenueHub['val']) {
                $topRevenueHub = ['name' => $p->name_ar, 'val' => $pRev];
            }
        }

        // و) مؤشر كفاءة وضغط الساحات (Yard Risk & Stock Density)
        $totalContDet = (int) ContainerStatusDetail::whereHas('record', fn($q) => $q->when($currentYear, fn($sq) => $sq->where('fiscal_year_id', $currentYear->id)))->sum('count');
        $dangerContDet = (int) ContainerStatusDetail::whereHas('record', fn($q) => $q->where('container_type', 'dangerous')->when($currentYear, fn($sq) => $sq->where('fiscal_year_id', $currentYear->id)))->sum('count');
        $contRiskPercent = $totalContDet > 0 ? round(($dangerContDet / $totalContDet) * 100, 1) : 0;

        return [
            'currentYear'         => $currentYear?->year ?? date('Y'),
            'prevYear'            => $prevYear?->year ?? (date('Y') - 1),
            'monthsCount'         => $monthsCount,
            'currTons'            => $currTons,
            'currRev'             => $currRev,
            'currShips'           => $currShips,
            'currTotalTeu'        => $currTotalTeu,
            'revPerTon'           => $revPerTon,
            'revPerTonDiff'       => $revPerTonDiff,
            'payloadPerShip'      => $payloadPerShip,
            'payloadDiff'         => $payloadDiff,
            'teuExportRatio'      => $teuExportRatio,
            'currImpTeu'          => $currImpTeu,
            'currExpTeu'          => $currExpTeu,
            'projectedAnnualTons' => $projectedAnnualTons,
            'projectedAnnualRev'  => $projectedAnnualRev,
            'topContainerHub'     => $topContainerHub,
            'topCargoHub'         => $topCargoHub,
            'topOilHub'           => $topOilHub,
            'topRevenueHub'       => $topRevenueHub,
            'contRiskPercent'     => $contRiskPercent,
            'totalContDet'        => $totalContDet,
            'dangerContDet'       => $dangerContDet,
        ];
    }
}
