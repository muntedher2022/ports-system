<?php

namespace App\Services;

use App\Models\FiscalYear;
use App\Models\Month;
use App\Models\RevenueCenter;
use App\Models\RevenueRecord;

class RevenueAnalyticsService
{
    /**
     * حساب مقارنة الإيراد لكل التشكيلات (المطابقة لورقة "مقارنة الايراد لكل التشكيلات")
     * مقارنة سنة بسنة (2025 مقابل 2026) لكل شهر ولكل مركز إيراد + إجمالي الشركة
     */
    public function getYearOverYearRevenueComparison(int $prevYearId, int $currYearId): array
    {
        $centers = RevenueCenter::where('is_active', true)->orderBy('sort_order')->get();
        $months  = Month::orderBy('month_number')->get();

        $prevRecords = RevenueRecord::where('fiscal_year_id', $prevYearId)->get()->groupBy(fn ($r) => $r->revenue_center_id . '_' . $r->month_id);
        $currRecords = RevenueRecord::where('fiscal_year_id', $currYearId)->get()->groupBy(fn ($r) => $r->revenue_center_id . '_' . $r->month_id);

        $tables = [];

        // 1. جداول المراكز السبعة
        foreach ($centers as $center) {
            $rows = [];
            $totalPrev = 0;
            $totalCurr = 0;

            foreach ($months as $month) {
                $pRec = $prevRecords->get($center->id . '_' . $month->id)?->first();
                $cRec = $currRecords->get($center->id . '_' . $month->id)?->first();

                $pVal = $pRec ? (float) $pRec->gross_revenue : 0;
                $cVal = $cRec ? (float) $cRec->gross_revenue : 0;
                $diff = $cVal - $pVal;
                $pct  = $pVal > 0 ? (($diff / $pVal) * 100) : 0;

                $rows[] = [
                    'month_name' => $month->name_ar,
                    'prev_val'   => $pVal,
                    'curr_val'   => $cVal,
                    'diff'       => $diff,
                    'percent'    => round($pct, 2),
                ];

                $totalPrev += $pVal;
                $totalCurr += $cVal;
            }

            $totalDiff = $totalCurr - $totalPrev;
            $totalPct  = $totalPrev > 0 ? (($totalDiff / $totalPrev) * 100) : 0;

            $tables[$center->id] = [
                'center_name' => $center->name_ar,
                'rows'        => $rows,
                'total_prev'  => $totalPrev,
                'total_curr'  => $totalCurr,
                'total_diff'  => $totalDiff,
                'total_pct'   => round($totalPct, 2),
            ];
        }

        // 2. جدول إجمالي الشركة ككل
        $companyRows = [];
        $companyTotalPrev = 0;
        $companyTotalCurr = 0;

        foreach ($months as $month) {
            $pMonthSum = 0;
            $cMonthSum = 0;

            foreach ($centers as $center) {
                $pRec = $prevRecords->get($center->id . '_' . $month->id)?->first();
                $cRec = $currRecords->get($center->id . '_' . $month->id)?->first();

                $pMonthSum += $pRec ? (float) $pRec->gross_revenue : 0;
                $cMonthSum += $cRec ? (float) $cRec->gross_revenue : 0;
            }

            $diff = $cMonthSum - $pMonthSum;
            $pct  = $pMonthSum > 0 ? (($diff / $pMonthSum) * 100) : 0;

            $companyRows[] = [
                'month_name' => $month->name_ar,
                'prev_val'   => $pMonthSum,
                'curr_val'   => $cMonthSum,
                'diff'       => $diff,
                'percent'    => round($pct, 2),
            ];

            $companyTotalPrev += $pMonthSum;
            $companyTotalCurr += $cMonthSum;
        }

        $companyTotalDiff = $companyTotalCurr - $companyTotalPrev;
        $companyTotalPct  = $companyTotalPrev > 0 ? (($companyTotalDiff / $companyTotalPrev) * 100) : 0;

        $tables['company'] = [
            'center_name' => 'إجمالي الشركة ككل (الموانئ السبعة والمقر)',
            'rows'        => $companyRows,
            'total_prev'  => $companyTotalPrev,
            'total_curr'  => $companyTotalCurr,
            'total_diff'  => $companyTotalDiff,
            'total_pct'   => round($companyTotalPct, 2),
        ];

        return [
            'prevYear' => FiscalYear::find($prevYearId)?->year,
            'currYear' => FiscalYear::find($currYearId)?->year,
            'centers'  => $centers,
            'tables'   => $tables,
        ];
    }
}
