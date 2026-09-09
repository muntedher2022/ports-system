<?php

namespace App\Filament\Pages;

use App\Enums\NavigationGroup;
use App\Models\FiscalYear;
use App\Models\Month;
use App\Models\Port;
use App\Services\PortAnalyticsService;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;

class CapacityComparison extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;
    protected static ?string $navigationLabel = 'مقارنة الطاقة الإنتاجية';
    protected static ?string $title = 'مقارنة الأداء للطاقة الإنتاجية للموانئ الأربعة بين عامين';
    protected static string|\UnitEnum|null $navigationGroup = NavigationGroup::Analytics;
    protected static ?int $navigationSort = 2;

    public static function canAccess(): bool
    {
        $user = Auth::user();
        if (! $user) return false;

        if ($user->hasRole(['super_admin', 'المدير العام', 'general_manager']) || $user->user_type === 'general_manager') {
            return true;
        }

        return $user->can('View:CapacityComparison')
            || $user->hasRole(['مسؤول المتابعة المركزية والعمليات', 'operations_manager'])
            || in_array($user->user_type, ['operations_manager']);
    }

    protected string $view = 'filament.pages.capacity-comparison';

    public ?int $prevFiscalYearId = null;
    public ?int $currFiscalYearId = null;
    public ?int $selectedMonthNumber = 7; // تموز افتراضياً

    public function mount(): void
    {
        $years = FiscalYear::orderBy('year', 'desc')->get();

        $this->currFiscalYearId = $years->firstWhere('is_current', true)?->id ?? $years->first()?->id;
        $this->prevFiscalYearId = $years->where('id', '!=', $this->currFiscalYearId)->first()?->id ?? $this->currFiscalYearId;
    }

    public function getDataProperty(): array
    {
        $service = app(PortAnalyticsService::class);

        $fiscalYears = FiscalYear::orderBy('year', 'desc')->get();
        $ports       = Port::where('is_active', true)->whereHas('monthlyPortRecords')->orderBy('sort_order')->get();
        $months      = Month::orderBy('month_number')->get();

        $prevYear = FiscalYear::find($this->prevFiscalYearId)?->year ?? 2025;
        $currYear = FiscalYear::find($this->currFiscalYearId)?->year ?? 2026;
        $monthName = Month::where('month_number', $this->selectedMonthNumber)->first()?->name_ar ?? 'تموز';

        // 1. جداول كل ميناء على حدة
        $portTables = [];
        foreach ($ports as $port) {
            $comparison = $service->getCapacityComparison(
                (int) $this->prevFiscalYearId,
                (int) $this->currFiscalYearId,
                (int) $this->selectedMonthNumber,
                $port->id
            );

            $portTables[] = [
                'port_name'   => $port->name_ar,
                'comparison'  => $comparison['comparison'],
            ];
        }

        // 2. جدول إجمالي الموانئ الأربعة معاً (Company Total)
        $companyComparison = $service->getCapacityComparison(
            (int) $this->prevFiscalYearId,
            (int) $this->currFiscalYearId,
            (int) $this->selectedMonthNumber,
            null
        );

        return [
            'fiscalYears'       => $fiscalYears,
            'months'            => $months,
            'prevYear'          => $prevYear,
            'currYear'          => $currYear,
            'monthName'         => $monthName,
            'portTables'        => $portTables,
            'companyComparison' => $companyComparison['comparison'],
        ];
    }
}
