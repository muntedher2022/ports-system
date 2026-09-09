<?php

namespace App\Filament\Pages;

use App\Enums\NavigationGroup;
use App\Models\FiscalYear;
use App\Services\RevenueAnalyticsService;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;

class RevenueComparison extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedScale;
    protected static ?string $navigationLabel = 'مقارنة الإيراد لكل التشكيلات';
    protected static ?string $title = 'مقارنة الإيراد لكل التشكيلات بين عامين';
    protected static string|\UnitEnum|null $navigationGroup = NavigationGroup::Analytics;
    protected static ?int $navigationSort = 3;

    public static function canAccess(): bool
    {
        $user = Auth::user();
        if (! $user) return false;

        if ($user->hasRole(['super_admin', 'المدير العام', 'general_manager']) || $user->user_type === 'general_manager') {
            return true;
        }

        return $user->can('View:RevenueComparison')
            || $user->hasRole(['مسؤول الإيراد المالي', 'finance_manager'])
            || in_array($user->user_type, ['finance_manager']);
    }

    protected string $view = 'filament.pages.revenue-comparison';

    public ?int $prevFiscalYearId = null;
    public ?int $currFiscalYearId = null;

    public function mount(): void
    {
        $years = FiscalYear::orderBy('year', 'desc')->get();

        $this->currFiscalYearId = $years->firstWhere('is_current', true)?->id ?? $years->first()?->id;
        $this->prevFiscalYearId = $years->where('id', '!=', $this->currFiscalYearId)->first()?->id ?? $this->currFiscalYearId;
    }

    public function getDataProperty(): array
    {
        $service = app(RevenueAnalyticsService::class);

        $fiscalYears = FiscalYear::orderBy('year', 'desc')->get();

        $comparison = $service->getYearOverYearRevenueComparison(
            (int) $this->prevFiscalYearId,
            (int) $this->currFiscalYearId
        );

        return [
            'fiscalYears' => $fiscalYears,
            'prevYear'    => $comparison['prevYear'],
            'currYear'    => $comparison['currYear'],
            'tables'      => $comparison['tables'],
        ];
    }
}
