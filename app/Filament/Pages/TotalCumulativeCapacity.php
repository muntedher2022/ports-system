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

class TotalCumulativeCapacity extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedCalculator;
    protected static ?string $navigationLabel = 'إجمالي الطاقة الإنتاجية';
    protected static ?string $title = 'إجمالي الطاقة الإنتاجية للموانئ';
    protected static string|\UnitEnum|null $navigationGroup = NavigationGroup::Analytics;
    protected static ?int $navigationSort = 1;

    public static function canAccess(): bool
    {
        $user = Auth::user();
        if (! $user) return false;

        if ($user->hasRole(['super_admin', 'المدير العام', 'general_manager']) || $user->user_type === 'general_manager') {
            return true;
        }

        return $user->can('View:TotalCumulativeCapacity')
            || $user->hasRole(['مسؤول المتابعة المركزية والعمليات', 'operations_manager'])
            || in_array($user->user_type, ['general_manager', 'operations_manager']);
    }

    protected string $view = 'filament.pages.total-cumulative-capacity';

    public ?int $selectedFiscalYearId = null;
    public ?int $selectedMonthNumber = 7; // تموز افتراضياً كما في ملف الإكسل
    public ?int $selectedPortId = null; // null = إجمالي الموانئ الأربعة

    public function mount(): void
    {
        $this->selectedFiscalYearId = FiscalYear::where('is_current', true)->first()?->id
            ?? FiscalYear::orderBy('year', 'desc')->first()?->id;

        $firstPort = Port::where('is_active', true)->orderBy('sort_order')->first();
        $this->selectedPortId = $firstPort?->id;
    }

    public function getDataProperty(): array
    {
        $service = app(PortAnalyticsService::class);

        $fiscalYears = FiscalYear::orderBy('year', 'desc')->get();
        $ports       = Port::where('is_active', true)->whereHas('monthlyPortRecords')->orderBy('sort_order')->get();
        $months      = Month::orderBy('month_number')->get();

        $selectedYear = FiscalYear::find($this->selectedFiscalYearId);
        $selectedMonth = Month::where('month_number', $this->selectedMonthNumber)->first();
        $selectedPort = $this->selectedPortId ? Port::find($this->selectedPortId) : null;
        $isAllPorts   = empty($this->selectedPortId);

        $summary = $service->getCumulativeSummary(
            (int) $this->selectedFiscalYearId,
            (int) $this->selectedMonthNumber,
            $this->selectedPortId ? (int) $this->selectedPortId : null
        );

        return [
            'fiscalYears'   => $fiscalYears,
            'ports'         => $ports,
            'months'        => $months,
            'selectedYear'  => $selectedYear?->year ?? 2026,
            'selectedMonth' => $selectedMonth?->name_ar ?? 'تموز',
            'selectedPort'  => $selectedPort ? $selectedPort->name_ar : 'الموانئ الأربعة مجتمعة (إجمالي الشركة)',
            'isAllPorts'    => $isAllPorts,
            'summary'       => $summary,
        ];
    }
}
