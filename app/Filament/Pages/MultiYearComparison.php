<?php

namespace App\Filament\Pages;

use App\Enums\NavigationGroup;
use App\Models\FiscalYear;
use App\Models\Month;
use App\Models\MonthlyPortRecord;
use App\Models\Port;
use App\Models\RevenueCenter;
use App\Models\RevenueRecord;
use App\Services\PortAnalyticsService;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;

class MultiYearComparison extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedPresentationChartLine;
    protected static ?string $navigationLabel = 'مقارنة الأداء متعددة السنوات';
    protected static ?string $title = 'مقارنة الأداء الشاملة للسنوات المتعددة (3 سنوات فأكثر)';
    protected static string|\UnitEnum|null $navigationGroup = NavigationGroup::Analytics;
    protected static ?int $navigationSort = 5;

    public static function canAccess(): bool
    {
        $user = Auth::user();
        if (! $user) return false;

        if ($user->hasRole(['super_admin', 'المدير العام', 'general_manager']) || $user->user_type === 'general_manager') {
            return true;
        }

        return $user->can('View:MultiYearComparison')
            || $user->hasRole(['مسؤول المتابعة المركزية والعمليات', 'operations_manager', 'مسؤول الإيراد المالي', 'finance_manager'])
            || in_array($user->user_type, ['general_manager', 'operations_manager', 'finance_manager']);
    }

    protected string $view = 'filament.pages.multi-year-comparison';

    // ─── فلاتر الاختيار ───
    public string $comparisonType = 'capacity'; // 'capacity' أو 'revenue'
    public string $periodScope = 'month';       // 'month' (شهر محدد) أو 'full_year' (سنة كاملة/تراكمي)
    public ?int $selectedMonthNumber = 7;       // تموز افتراضياً
    public ?int $selectedPortId = null;         // null = عموم الموانئ
    public array $selectedYearIds = [];         // مصفوفة السنوات المختارة للمقارنة

    public function mount(): void
    {
        // اختيار كافة السنوات المتوفرة افتراضياً
        $this->selectedYearIds = FiscalYear::orderBy('year', 'asc')->pluck('id')->toArray();
    }

    public function getDataProperty(): array
    {
        $allYears = FiscalYear::orderBy('year', 'asc')->get();
        $selectedYears = FiscalYear::whereIn('id', $this->selectedYearIds)->orderBy('year', 'asc')->get();
        $ports = Port::where('is_active', true)->whereHas('monthlyPortRecords')->orderBy('sort_order')->get();
        $centers = RevenueCenter::where('is_active', true)->orderBy('sort_order')->get();
        $months = Month::orderBy('month_number')->get();

        $monthName = Month::where('month_number', $this->selectedMonthNumber)->first()?->name_ar ?? 'تموز';
        $endMonthNum = $this->periodScope === 'full_year' ? 12 : (int) $this->selectedMonthNumber;

        $analyticsService = app(PortAnalyticsService::class);

        // ─── 1. بيانات مقارنة الطاقة الإنتاجية التشغيلية ───
        $capacityMetrics = [
            'total_ships'          => 'إجمالي عدد البواخر والناقلات (سفينة)',
            'container_ships'      => 'عدد بواخر الحاويات',
            'cargo_ships'          => 'عدد بواخر البضائع المتنوعة',
            'oil_tankers'          => 'عدد الناقلات النفطية',
            'total_containers'     => 'إجمالي عدد الحاويات (مستوردة + TEU)',
            'imported_containers'  => 'عدد الحاويات المستوردة (حاوية)',
            'imported_teu'         => 'الحاويات المستوردة مكافئ (TEU)',
            'exported_containers'  => 'إجمالي الحاويات المصدرة (فارغ + مليان)',
            'exported_teu'         => 'الحاويات المصدرة مكافئ (TEU)',
            'total_tonnage'        => 'الطاقة الإنتاجية الكلية (طن)',
            'imported_cargo_tons'  => 'وزن البضائع المستوردة بالطن (حاويات + سيارات)',
            'exported_cargo_tons'  => 'وزن البضائع المصدرة بالطن',
            'general_cargo_tons'   => 'وزن البضائع المتنوعة بالطن',
            'oil_total_tons'       => 'إجمالي أطنان النفط والمشتقات (طن)',
            'imported_cars'        => 'عدد السيارات المستوردة (سيارة)',
            'daily_avg_tonnage'    => 'المعدل اليومي لمناولة البضائع (طن/يوم)',
            'monthly_avg_tonnage'  => 'المعدل الشهري لمناولة البضائع (طن/شهر)',
        ];

        $capacityRows = [];
        foreach ($capacityMetrics as $key => $label) {
            $rowValues = [];
            foreach ($selectedYears as $year) {
                if ($this->periodScope === 'month') {
                    // بيانات شهر محدد فقط
                    $q = MonthlyPortRecord::where('fiscal_year_id', $year->id)
                        ->where('month_id', $this->selectedMonthNumber);
                    if ($this->selectedPortId) {
                        $q->where('port_id', $this->selectedPortId);
                    }
                    $records = $q->get();

                    $val = match($key) {
                        'total_ships'         => $records->sum('total_container_ships') + $records->sum('general_cargo_ships') + $records->sum('oil_tankers_count') + $records->sum('car_carrier_ships'),
                        'container_ships'     => $records->sum('total_container_ships'),
                        'cargo_ships'         => $records->sum('general_cargo_ships'),
                        'oil_tankers'         => $records->sum('oil_tankers_count'),
                        'total_containers'    => $records->sum('imported_containers_count') + $records->sum('exported_containers_count'),
                        'imported_containers' => $records->sum('imported_containers_count'),
                        'imported_teu'        => $records->sum('imported_teu'),
                        'exported_containers' => $records->sum('exported_containers_count'),
                        'exported_teu'        => $records->sum('exported_teu'),
                        'total_tonnage'       => (float) $records->sum('imported_containers_weight_tons') + (float) $records->sum('exported_full_weight_tons') + (float) $records->sum('general_cargo_weight_tons') + (float) $records->sum('oil_total_tons') + (float) $records->sum('imported_cars_weight_tons'),
                        'imported_cargo_tons' => (float) $records->sum('imported_containers_weight_tons') + (float) $records->sum('imported_cars_weight_tons'),
                        'exported_cargo_tons' => (float) $records->sum('exported_full_weight_tons'),
                        'general_cargo_tons'  => (float) $records->sum('general_cargo_weight_tons'),
                        'oil_total_tons'      => (float) $records->sum('oil_total_tons'),
                        'imported_cars'       => $records->sum('imported_cars_count'),
                        'daily_avg_tonnage'   => ((float) $records->sum('imported_containers_weight_tons') + (float) $records->sum('exported_full_weight_tons') + (float) $records->sum('general_cargo_weight_tons') + (float) $records->sum('oil_total_tons') + (float) $records->sum('imported_cars_weight_tons')) / 30,
                        'monthly_avg_tonnage' => (float) $records->sum('imported_containers_weight_tons') + (float) $records->sum('exported_full_weight_tons') + (float) $records->sum('general_cargo_weight_tons') + (float) $records->sum('oil_total_tons') + (float) $records->sum('imported_cars_weight_tons'),
                        default               => 0,
                    };
                } else {
                    // بيانات تراكمية لكامل السنة (أو لغاية الشهر المحدد)
                    $summary = $analyticsService->getCumulativeSummary($year->id, $endMonthNum, $this->selectedPortId);
                    $val = match($key) {
                        'total_ships'         => $summary['total_ships'] ?? 0,
                        'container_ships'     => $summary['container_ships'] ?? 0,
                        'cargo_ships'         => $summary['cargo_ships'] ?? 0,
                        'oil_tankers'         => $summary['oil_tankers'] ?? 0,
                        'total_containers'    => ($summary['imported_containers'] ?? 0) + ($summary['exported_containers'] ?? 0),
                        'imported_containers' => $summary['imported_containers'] ?? 0,
                        'imported_teu'        => $summary['imported_teu'] ?? 0,
                        'exported_containers' => $summary['exported_containers'] ?? 0,
                        'exported_teu'        => $summary['exported_teu'] ?? 0,
                        'total_tonnage'       => $summary['total_tonnage'] ?? 0,
                        'imported_cargo_tons' => $summary['imported_cargo_tons'] ?? 0,
                        'exported_cargo_tons' => $summary['exported_cargo_tons'] ?? 0,
                        'general_cargo_tons'  => $summary['general_cargo_tons'] ?? 0,
                        'oil_total_tons'      => $summary['oil_total_tons'] ?? 0,
                        'imported_cars'       => $summary['imported_cars'] ?? 0,
                        'daily_avg_tonnage'   => $summary['daily_avg_tonnage'] ?? 0,
                        'monthly_avg_tonnage' => $summary['monthly_avg_tonnage'] ?? 0,
                        default               => 0,
                    };
                }
                $rowValues[$year->id] = (float) $val;
            }

            // حساب التغير الذكي: الاعتماد على أول سنة وآخر سنة بهما بيانات فعلية (> 0)
            $nonZeroVals = array_filter($rowValues, fn($v) => $v > 0);
            if (count($nonZeroVals) >= 2) {
                $baseVal = reset($nonZeroVals);
                $latestVal = end($nonZeroVals);
                $diff = $latestVal - $baseVal;
                $pctChange = ($diff / $baseVal) * 100;
            } elseif (count($rowValues) >= 2) {
                $baseVal = reset($rowValues);
                $latestVal = end($rowValues);
                $diff = $latestVal - $baseVal;
                $pctChange = $baseVal > 0 ? (($diff / $baseVal) * 100) : null;
            } else {
                $diff = 0;
                $pctChange = 0;
            }

            $capacityRows[] = [
                'metric_key' => $key,
                'label'      => $label,
                'is_primary' => in_array($key, ['total_tonnage', 'total_ships', 'total_containers', 'oil_total_tons']),
                'values'     => $rowValues,
                'diff'       => $diff,
                'pct_change' => $pctChange,
            ];
        }

        // ─── 2. بيانات مقارنة الإيرادات المالية ───
        $revenueRows = [];
        foreach ($centers as $center) {
            $rowValues = [];
            foreach ($selectedYears as $year) {
                $q = RevenueRecord::where('fiscal_year_id', $year->id)
                    ->where('revenue_center_id', $center->id);

                if ($this->periodScope === 'month') {
                    $q->where('month_id', $this->selectedMonthNumber);
                } else {
                    $q->where('month_id', '<=', $endMonthNum);
                }

                $rowValues[$year->id] = (float) $q->sum('gross_revenue');
            }

            $nonZeroVals = array_filter($rowValues, fn($v) => $v > 0);
            if (count($nonZeroVals) >= 2) {
                $baseVal = reset($nonZeroVals);
                $latestVal = end($nonZeroVals);
                $diff = $latestVal - $baseVal;
                $pctChange = ($diff / $baseVal) * 100;
            } elseif (count($rowValues) >= 2) {
                $baseVal = reset($rowValues);
                $latestVal = end($rowValues);
                $diff = $latestVal - $baseVal;
                $pctChange = $baseVal > 0 ? (($diff / $baseVal) * 100) : null;
            } else {
                $diff = 0;
                $pctChange = 0;
            }

            $revenueRows[] = [
                'center_name' => $center->name_ar,
                'values'      => $rowValues,
                'diff'        => $diff,
                'pct_change'  => $pctChange,
            ];
        }

        // صف مجموع الإيراد الكلي لعموم الشركة
        $grandGrossTotals = [];
        $grandNetTotals = [];
        foreach ($selectedYears as $year) {
            $q = RevenueRecord::where('fiscal_year_id', $year->id);
            if ($this->periodScope === 'month') {
                $q->where('month_id', $this->selectedMonthNumber);
            } else {
                $q->where('month_id', '<=', $endMonthNum);
            }
            $grandGrossTotals[$year->id] = (float) $q->sum('gross_revenue');
            $grandNetTotals[$year->id] = (float) $q->sum('net_revenue');
        }

        // الإيراد الكلي
        $nonZeroGross = array_filter($grandGrossTotals, fn($v) => $v > 0);
        if (count($nonZeroGross) >= 2) {
            $baseGross = reset($nonZeroGross);
            $latestGross = end($nonZeroGross);
            $grossDiff = $latestGross - $baseGross;
            $grossPct = ($grossDiff / $baseGross) * 100;
        } else {
            $baseGross = reset($grandGrossTotals);
            $latestGross = end($grandGrossTotals);
            $grossDiff = $latestGross - $baseGross;
            $grossPct = $baseGross > 0 ? (($grossDiff / $baseGross) * 100) : null;
        }

        // الإيراد الصافي
        $nonZeroNet = array_filter($grandNetTotals, fn($v) => $v > 0);
        if (count($nonZeroNet) >= 2) {
            $baseNet = reset($nonZeroNet);
            $latestNet = end($nonZeroNet);
            $netDiff = $latestNet - $baseNet;
            $netPct = ($netDiff / $baseNet) * 100;
        } else {
            $baseNet = reset($grandNetTotals);
            $latestNet = end($grandNetTotals);
            $netDiff = $latestNet - $baseNet;
            $netPct = $baseNet > 0 ? (($netDiff / $baseNet) * 100) : null;
        }

        return [
            'allYears'          => $allYears,
            'selectedYears'     => $selectedYears,
            'ports'             => $ports,
            'centers'           => $centers,
            'months'            => $months,
            'monthName'         => $monthName,
            'capacityRows'      => $capacityRows,
            'revenueRows'       => $revenueRows,
            'grandGrossTotals'  => $grandGrossTotals,
            'grandNetTotals'    => $grandNetTotals,
            'grossDiff'         => $grossDiff,
            'grossPct'          => $grossPct,
            'netDiff'           => $netDiff,
            'netPct'            => $netPct,
        ];
    }
}
