<?php

namespace App\Filament\Widgets;

use App\Models\CargoStatusDetail;
use App\Models\CargoStatusRecord;
use App\Models\ContainerItem;
use App\Models\ContainerStatusDetail;
use App\Models\ContainerStatusRecord;
use App\Models\FiscalYear;
use App\Models\Port;
use App\Services\ContainerExcelImportService;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class RegulatoryRiskIntelligenceWidget extends Widget
{
    protected static ?string $heading = '🛡️ مركز التحليلات الرقابية وإدارة المخاطر التشغيلية';
    protected static ?int $sort = 9;
    protected int | string | array $columnSpan = 'full';
    protected string $view = 'filament.widgets.regulatory-risk-intelligence-widget';

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

    public function getIntelligenceData(): array
    {
        $user   = Auth::user();
        $portId = ($user?->isPortRestricted() && $user?->port_id) ? $user->port_id : null;

        $currentYear = FiscalYear::where('is_current', true)->first()
            ?? FiscalYear::orderBy('year', 'desc')->first();

        // 1. حساب مؤشر مطابقة معيار ISO 6346 للحاويات الفردية (مع كاش خفيف لمدة 10 دقائق لسرعة الأداء)
        $cacheKey = 'dashboard_iso_stats_' . ($portId ?? 'all') . '_' . ($currentYear?->id ?? 'curr');
        $isoStats = Cache::remember($cacheKey, 300, function () use ($portId, $currentYear) {
            $itemsQuery = ContainerItem::query()
                ->when($portId, fn($q) => $q->where('port_id', $portId))
                ->when($currentYear, fn($q) => $q->where('fiscal_year_id', $currentYear->id));

            $totalItems = $itemsQuery->count();
            if ($totalItems === 0) {
                // محاولة فحص كل العناصر إذا لم توجد في السنة الحالية
                $totalItems = ContainerItem::count();
                $items = ContainerItem::select('container_number')->limit(2000)->get();
            } else {
                $items = $itemsQuery->select('container_number')->limit(2000)->get();
            }

            $validCount = 0;
            $invalidCount = 0;

            foreach ($items as $item) {
                $res = ContainerExcelImportService::validateIsoContainerNumber($item->container_number);
                if (!empty($res['is_valid'])) {
                    $validCount++;
                } else {
                    $invalidCount++;
                }
            }

            $examined = $validCount + $invalidCount;
            $complianceRate = $examined > 0 ? round(($validCount / $examined) * 100, 1) : 100;

            return [
                'total_examined'   => $examined,
                'valid_count'      => $validCount,
                'invalid_count'    => $invalidCount,
                'compliance_rate'  => $complianceRate,
            ];
        });

        // 2. مؤشر كثافة الخطورة (Dangerous Cargo & Container Density)
        $abandonedCont = (int) ContainerStatusDetail::whereHas('record', fn($q) => $q->where('container_type', 'abandoned')->when($currentYear, fn($sq) => $sq->where('fiscal_year_id', $currentYear->id))->when($portId, fn($sq) => $sq->where('port_id', $portId)))->sum('count');
        $dangerousCont = (int) ContainerStatusDetail::whereHas('record', fn($q) => $q->where('container_type', 'dangerous')->when($currentYear, fn($sq) => $sq->where('fiscal_year_id', $currentYear->id))->when($portId, fn($sq) => $sq->where('port_id', $portId)))->sum('count');
        
        $abandonedCargo = (int) CargoStatusDetail::whereHas('record', fn($q) => $q->where('cargo_type', 'abandoned')->when($currentYear, fn($sq) => $sq->where('fiscal_year_id', $currentYear->id))->when($portId, fn($sq) => $sq->where('port_id', $portId)))->sum('count');
        $dangerousCargo = (int) CargoStatusDetail::whereHas('record', fn($q) => $q->where('cargo_type', 'dangerous')->when($currentYear, fn($sq) => $sq->where('fiscal_year_id', $currentYear->id))->when($portId, fn($sq) => $sq->where('port_id', $portId)))->sum('count');

        $totalContVolume = $abandonedCont + $dangerousCont;
        $totalCargoVolume = $abandonedCargo + $dangerousCargo;

        $contDangerRate = $totalContVolume > 0 ? round(($dangerousCont / $totalContVolume) * 100, 1) : 0;
        $cargoDangerRate = $totalCargoVolume > 0 ? round(($dangerousCargo / $totalCargoVolume) * 100, 1) : 0;

        // 3. تحليل التقادم والأرصدة المعمرة (Aging / Long-stay Inventory)
        // تفريغ الحاويات والمواد التابعة لسنوات قديمة (2004-2022)
        $oldYearsLabels = ['2004-2015', '2016', '2017', '2018', '2019', '2020', '2021', '2022', '2023'];
        $oldContCount = (int) ContainerStatusDetail::whereIn('year_label', $oldYearsLabels)
            ->whereHas('record', fn($q) => $q->when($currentYear, fn($sq) => $sq->where('fiscal_year_id', $currentYear->id))->when($portId, fn($sq) => $sq->where('port_id', $portId)))
            ->sum('count');

        $oldCargoCount = (int) CargoStatusDetail::whereIn('year_label', $oldYearsLabels)
            ->whereHas('record', fn($q) => $q->when($currentYear, fn($sq) => $sq->where('fiscal_year_id', $currentYear->id))->when($portId, fn($sq) => $sq->where('port_id', $portId)))
            ->sum('count');

        // 4. مؤشر الجاهزية والامتثال العام
        $healthScore = round(($isoStats['compliance_rate'] * 0.5) + (max(0, 100 - $contDangerRate * 2) * 0.25) + (max(0, 100 - ($totalContVolume > 0 ? ($oldContCount / $totalContVolume) * 100 : 0)) * 0.25), 0);

        return [
            'currentYear'     => $currentYear?->year ?? date('Y'),
            'isoStats'        => $isoStats,
            'dangerousCont'   => $dangerousCont,
            'contDangerRate'  => $contDangerRate,
            'dangerousCargo'  => $dangerousCargo,
            'cargoDangerRate' => $cargoDangerRate,
            'oldContCount'    => $oldContCount,
            'oldCargoCount'   => $oldCargoCount,
            'healthScore'     => min(100, max(40, $healthScore)),
            'totalContVolume' => $totalContVolume,
            'totalCargoVolume'=> $totalCargoVolume,
        ];
    }
}
