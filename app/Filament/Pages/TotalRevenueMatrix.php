<?php

namespace App\Filament\Pages;

use App\Enums\NavigationGroup;
use App\Models\FiscalYear;
use App\Models\Month;
use App\Models\RevenueCenter;
use App\Models\RevenueRecord;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;

class TotalRevenueMatrix extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedTableCells;
    protected static ?string $navigationLabel = 'جدول الإيراد الكلي للمراكز السبعة';
    protected static ?string $title = 'الإيراد الكلي والصافي لمراكز الإيراد السبعة';
    protected static string|\UnitEnum|null $navigationGroup = NavigationGroup::Analytics;
    protected static ?int $navigationSort = 4;

    public static function canAccess(): bool
    {
        $user = Auth::user();
        if (! $user) return false;

        if ($user->hasRole(['super_admin', 'المدير العام', 'general_manager']) || $user->user_type === 'general_manager') {
            return true;
        }

        return $user->can('View:TotalRevenueMatrix')
            || $user->hasRole(['مسؤول الإيراد المالي', 'finance_manager'])
            || in_array($user->user_type, ['general_manager', 'finance_manager']);
    }

    protected string $view = 'filament.pages.total-revenue-matrix';

    public ?int $selectedFiscalYearId = null;

    public function mount(): void
    {
        $this->selectedFiscalYearId = FiscalYear::where('is_current', true)->first()?->id
            ?? FiscalYear::orderBy('year', 'desc')->first()?->id;
    }

    public function getDataProperty(): array
    {
        $fiscalYears = FiscalYear::orderBy('year', 'desc')->get();
        $selectedYear = FiscalYear::find($this->selectedFiscalYearId);
        $allCenters = RevenueCenter::where('is_active', true)->orderBy('sort_order')->get();
        $months = Month::orderBy('month_number')->get();

        $records = RevenueRecord::where('fiscal_year_id', $this->selectedFiscalYearId)->get();

        $centers = $allCenters->filter(function ($center) use ($records) {
            return $records->where('revenue_center_id', $center->id)->sum('gross_revenue') > 0
                || $records->where('revenue_center_id', $center->id)->sum('net_revenue') > 0;
        })->values();

        if ($centers->isEmpty()) {
            $centers = $allCenters;
        }

        $recordsGrouped = $records->groupBy(fn ($r) => $r->month_id . '_' . $r->revenue_center_id);

        $matrix = [];
        $runningTotal = 0;
        $centerTotals = array_fill_keys($centers->pluck('id')->toArray(), 0);
        $grandGrossTotal = 0;
        $grandNetTotal = 0;

        foreach ($months as $month) {
            $monthGross = 0;
            $monthNet = 0;
            $centersData = [];
            $hasData = false;

            foreach ($centers as $center) {
                $key = $month->id . '_' . $center->id;
                $record = $recordsGrouped->get($key)?->first();

                $gross = $record ? (float) $record->gross_revenue : 0;
                $net   = $record ? (float) $record->net_revenue : 0;

                if ($record && ($gross > 0 || $net > 0)) {
                    $hasData = true;
                }

                $centersData[$center->id] = $gross;
                $monthGross += $gross;
                $monthNet   += $net;
                $centerTotals[$center->id] += $gross;
            }

            if ($hasData) {
                $runningTotal += $monthGross;
            }

            $grandGrossTotal += $monthGross;
            $grandNetTotal   += $monthNet;

            $matrix[] = [
                'month_name'             => $month->name_ar,
                'centers'                => $centersData,
                'monthly_gross_total'    => $monthGross,
                'monthly_net_total'      => $monthNet,
                'cumulative_gross_total' => $hasData ? $runningTotal : 0,
                'has_data'               => $hasData,
            ];
        }

        return [
            'fiscalYears'       => $fiscalYears,
            'selectedYear'      => $selectedYear?->year ?? 2026,
            'centers'           => $centers,
            'matrix'            => $matrix,
            'centerTotals'      => $centerTotals,
            'grandGrossTotal'   => $grandGrossTotal,
            'grandNetTotal'     => $grandNetTotal,
        ];
    }
}
