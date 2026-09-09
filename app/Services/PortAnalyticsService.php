<?php

namespace App\Services;

use App\Models\FiscalYear;
use App\Models\Month;
use App\Models\MonthlyPortRecord;
use App\Models\Port;

class PortAnalyticsService
{
    /**
     * حساب الطاقة الإنتاجية التراكمية (المطابقة لورقة total في الإكسل بالكامل 100%)
     */
    public function getCumulativeSummary(int $fiscalYearId, int $endMonthNumber, ?int $portId = null): array
    {
        $months = Month::where('month_number', '<=', $endMonthNumber)->pluck('id');

        $query = MonthlyPortRecord::where('fiscal_year_id', $fiscalYearId)
            ->whereIn('month_id', $months);

        if ($portId) {
            $query->where('port_id', $portId);
        }

        $records = $query->get();

        $totalContainerShips = $records->sum('total_container_ships');
        $generalCargoShips   = $records->sum('general_cargo_ships');
        $oilTankersCount     = $records->sum('oil_tankers_count');
        $carCarrierShips     = $records->sum('car_carrier_ships');
        $totalShips          = $totalContainerShips + $generalCargoShips + $oilTankersCount + $carCarrierShips;

        $importedWeight      = (float) $records->sum('imported_containers_weight_tons');
        $exportedWeight      = (float) $records->sum('exported_full_weight_tons');
        $generalCargoWeight  = (float) $records->sum('general_cargo_weight_tons');
        $carsWeight          = (float) $records->sum('imported_cars_weight_tons');

        // مطابقة معادلات الإكسل الدقيقة لورقة total:
        // C80 = مجموع وزن الحاويات (المستوردة C16)
        $totalContainersWeight = $importedWeight;

        // C81 = مجموع وزن البضائع (المصدرة مليانة K16 + بضائع متنوعة R16 + سيارات X16)
        $totalCargoWeight      = $exportedWeight + $generalCargoWeight + $carsWeight;

        $oilExportedWeight   = (float) $records->sum('oil_exported_tons');
        $oilImportedWeight   = (float) $records->sum('oil_imported_tons');
        $oilTotalWeight      = (float) $records->sum('oil_total_tons');

        // الطاقة الإنتاجية الكلية بالطن
        $totalTonnage = $importedWeight + $exportedWeight + $generalCargoWeight + $oilTotalWeight + $carsWeight;

        $monthsCount = max(1, $endMonthNumber);
        $daysCount   = $monthsCount * 30;

        $monthlyAverageWeight = $totalTonnage / $monthsCount;
        $dailyAverageWeight   = $monthlyAverageWeight / 30;

        $importedContainersCount = $records->sum('imported_containers_count');
        $importedTeu             = $records->sum('imported_teu');
        $exportedContainersCount = $records->sum('exported_containers_count');
        $exportedTeu             = $records->sum('exported_teu');
        $exportedFullCount       = $records->sum('exported_full_count');
        $exportedEmptyCount      = $records->sum('exported_empty_count');

        $totalTeu = $importedTeu + $exportedTeu;
        $totalRevenue = (float) $records->sum('total_revenue');

        // المعدلات الشهرية واليومية العامة للشركة (Rows 85-92 في الإكسل)
        $monthlyAvgShips    = $totalShips / $monthsCount;
        $dailyAvgShips      = $totalShips / $daysCount;

        $monthlyAvgTeu      = $totalTeu / $monthsCount;
        $dailyAvgTeu        = $totalTeu / $daysCount;

        $monthlyAvgOilExport = $oilExportedWeight / $monthsCount;
        $dailyAvgOilExport   = $oilExportedWeight / $daysCount;

        $monthlyAvgOilImport = $oilImportedWeight / $monthsCount;
        $dailyAvgOilImport   = $oilImportedWeight / $daysCount;

        return [
            'total_ships'               => $totalShips,
            'total_container_ships'     => $totalContainerShips,
            'general_cargo_ships'       => $generalCargoShips,
            'oil_tankers_count'         => $oilTankersCount,
            'car_carrier_ships'         => $carCarrierShips,
            'total_tonnage'             => $totalTonnage,
            'monthly_average_weight'    => $monthlyAverageWeight,
            'daily_average_weight'      => $dailyAverageWeight,
            'oil_exported_tons'         => $oilExportedWeight,
            'oil_imported_tons'         => $oilImportedWeight,
            'oil_total_tons'            => $oilTotalWeight,
            'imported_containers_count' => $importedContainersCount,
            'imported_teu'              => $importedTeu,
            'exported_containers_count' => $exportedContainersCount,
            'exported_teu'              => $exportedTeu,
            'exported_full_count'       => $exportedFullCount,
            'exported_empty_count'      => $exportedEmptyCount,
            'total_teu'                 => $totalTeu,
            'total_containers_weight'   => $totalContainersWeight,
            'total_cargo_weight'        => $totalCargoWeight,
            'general_cargo_weight_tons' => $generalCargoWeight,
            'imported_cars_weight_tons' => $carsWeight,
            'total_revenue'             => $totalRevenue,
            'months_count'              => $monthsCount,
            'days_count'                => $daysCount,
            // المعدلات
            'monthly_avg_ships'         => $monthlyAvgShips,
            'daily_avg_ships'           => $dailyAvgShips,
            'monthly_avg_teu'           => $monthlyAvgTeu,
            'daily_avg_teu'             => $dailyAvgTeu,
            'monthly_avg_oil_export'    => $monthlyAvgOilExport,
            'daily_avg_oil_export'      => $dailyAvgOilExport,
            'monthly_avg_oil_import'    => $monthlyAvgOilImport,
            'daily_avg_oil_import'      => $dailyAvgOilImport,
        ];
    }

    /**
     * مقارنة الطاقة الإنتاجية بين سنتين ماليتين
     */
    public function getCapacityComparison(int $prevYearId, int $currYearId, int $endMonthNumber, ?int $portId = null): array
    {
        $prevData = $this->getCumulativeSummary($prevYearId, $endMonthNumber, $portId);
        $currData = $this->getCumulativeSummary($currYearId, $endMonthNumber, $portId);

        $metricsConfig = [
            ['key' => 'total_ships',               'label' => 'عدد البواخر الكلي',                   'unit' => 'باخرة'],
            ['key' => 'total_tonnage',             'label' => 'الطاقة الانتاجية الكلية للميناء بالطن', 'unit' => 'طن'],
            ['key' => 'monthly_average_weight',    'label' => 'معدل الاوزان الشهري',                 'unit' => 'طن'],
            ['key' => 'daily_average_weight',      'label' => 'معدل الاوزان اليومي',                 'unit' => 'طن'],
            ['key' => 'oil_exported_tons',         'label' => 'تصدير مشتقات نفطية',                  'unit' => 'طن'],
            ['key' => 'oil_imported_tons',         'label' => 'استيراد مشتقات نفطية',                 'unit' => 'طن'],
            ['key' => 'imported_containers_count', 'label' => 'عدد الحاويات المستوردة',              'unit' => 'حاوية'],
            ['key' => 'imported_teu',              'label' => 'عدد الحاويات المستوردة TEU',          'unit' => 'حاوية'],
            ['key' => 'exported_containers_count', 'label' => 'عدد الحاويات المصدرة',                'unit' => 'حاوية'],
            ['key' => 'exported_teu',              'label' => 'عدد الحاويات المصدرة TEU',            'unit' => 'حاوية'],
            ['key' => 'exported_full_count',       'label' => 'عدد الحاويات المصدرة مليان',          'unit' => 'حاوية'],
            ['key' => 'exported_empty_count',      'label' => 'عدد الحاويات المصدرة فارغ',           'unit' => 'حاوية'],
            ['key' => 'total_teu',                 'label' => 'الطاقة الإنتاجية الكلية TEU',         'unit' => 'حاوية'],
            ['key' => 'total_revenue',             'label' => 'الإيراد الكلي للميناء',              'unit' => 'دينار'],
        ];

        $comparison = [];

        foreach ($metricsConfig as $cfg) {
            $prevVal = $prevData[$cfg['key']] ?? 0;
            $currVal = $currData[$cfg['key']] ?? 0;
            $diff    = $currVal - $prevVal;
            $percent = $prevVal > 0 ? (($diff / $prevVal) * 100) : 0;

            $comparison[] = [
                'label'      => $cfg['label'],
                'unit'       => $cfg['unit'],
                'prev_val'   => $prevVal,
                'curr_val'   => $currVal,
                'diff'       => $diff,
                'percent'    => round($percent, 2),
            ];
        }

        return [
            'prevYear'   => FiscalYear::find($prevYearId)?->year,
            'currYear'   => FiscalYear::find($currYearId)?->year,
            'endMonth'   => Month::where('month_number', $endMonthNumber)->first()?->name_ar,
            'comparison' => $comparison,
        ];
    }

    /**
     * حساب الانحراف المعياري ومؤشرات الاستقرار الإحصائية لسنة مالية محددة
     */
    public function getYearlyStandardDeviation(int $fiscalYearId, ?int $portId = null): array
    {
        $months = Month::orderBy('month_number')->get();
        $monthlyData = [];

        foreach ($months as $m) {
            $qPort = MonthlyPortRecord::where('fiscal_year_id', $fiscalYearId)->where('month_id', $m->id);
            if ($portId) {
                $qPort->where('port_id', $portId);
            }
            $portRecs = $qPort->get();

            $qRev = \App\Models\RevenueRecord::where('fiscal_year_id', $fiscalYearId)->where('month_id', $m->id);
            if ($portId) {
                $revCenter = \App\Models\RevenueCenter::where('port_id', $portId)->first();
                if ($revCenter) {
                    $qRev->where('revenue_center_id', $revCenter->id);
                }
            }
            $revRecs = $qRev->get();

            $tonnage = (float) $portRecs->sum('imported_containers_weight_tons') + 
                       (float) $portRecs->sum('exported_full_weight_tons') + 
                       (float) $portRecs->sum('general_cargo_weight_tons') + 
                       (float) $portRecs->sum('oil_total_tons') + 
                       (float) $portRecs->sum('imported_cars_weight_tons');

            $ships = (int) $portRecs->sum('total_container_ships') + 
                     (int) $portRecs->sum('general_cargo_ships') + 
                     (int) $portRecs->sum('oil_tankers_count') + 
                     (int) $portRecs->sum('car_carrier_ships');

            $teu = (int) $portRecs->sum('imported_teu') + (int) $portRecs->sum('exported_teu');
            $grossRev = (float) $revRecs->sum('gross_revenue');
            $netRev = (float) $revRecs->sum('net_revenue');

            $monthlyData[$m->month_number] = [
                'month_id'   => $m->id,
                'month_name' => $m->name_ar,
                'tonnage'    => $tonnage,
                'ships'      => $ships,
                'teu'        => $teu,
                'revenue'    => $grossRev,
                'net_revenue'=> $netRev,
            ];
        }

        return [
            'fiscal_year' => FiscalYear::find($fiscalYearId),
            'tonnage'     => $this->computeStats(array_column($monthlyData, 'tonnage')),
            'ships'       => $this->computeStats(array_column($monthlyData, 'ships')),
            'teu'         => $this->computeStats(array_column($monthlyData, 'teu')),
            'revenue'     => $this->computeStats(array_column($monthlyData, 'revenue')),
            'net_revenue' => $this->computeStats(array_column($monthlyData, 'net_revenue')),
            'monthly'     => $monthlyData,
        ];
    }

    /**
     * دالة رياضية لحساب المتوسط، التباين، الانحراف المعياري، ومعامل التشتت
     */
    public function computeStats(array $values): array
    {
        // استبعاد القيم الصفرية إذا كانت غير مدخلة بعد، أو الاحتفاظ بها
        $filtered = array_filter($values, fn($v) => $v > 0);
        $count = count($filtered);

        if ($count === 0) {
            return [
                'count'     => 0,
                'mean'      => 0,
                'variance'  => 0,
                'std_dev'   => 0,
                'cv'        => 0,
                'min'       => 0,
                'max'       => 0,
                'stability' => 'لا تتوفر بيانات',
            ];
        }

        $mean = array_sum($filtered) / $count;
        $variance = 0;
        foreach ($filtered as $val) {
            $variance += pow($val - $mean, 2);
        }
        $variance = $variance / $count;
        $stdDev = sqrt($variance);
        $cv = $mean > 0 ? (($stdDev / $mean) * 100) : 0;

        $stability = match(true) {
            $cv <= 15 => 'عالي الاستقرار (ممتاز)',
            $cv <= 30 => 'استقرار متوسط (طبيعي)',
            default   => 'تذبذب وتقلب مرتفع',
        };

        return [
            'count'     => $count,
            'mean'      => round($mean, 2),
            'variance'  => round($variance, 2),
            'std_dev'   => round($stdDev, 2),
            'cv'        => round($cv, 1),
            'min'       => min($filtered),
            'max'       => max($filtered),
            'stability' => $stability,
        ];
    }
}
