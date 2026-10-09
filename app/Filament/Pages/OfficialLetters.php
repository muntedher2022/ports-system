<?php

namespace App\Filament\Pages;

use App\Enums\NavigationGroup;
use App\Models\FiscalYear;
use App\Models\Month;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;

class OfficialLetters extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $navigationLabel = 'الكتب والمذكرات الرسمية';

    protected static ?string $title = 'إصدار الكتب والمذكرات الرسمية';

    protected static string|\UnitEnum|null $navigationGroup = NavigationGroup::Reports;

    protected static ?int $navigationSort = 0;

    public static function canAccess(): bool
    {
        $user = Auth::user();
        if (! $user) {
            return false;
        }

        if ($user->hasRole(['super_admin', 'المدير العام', 'general_manager']) || $user->user_type === 'general_manager') {
            return true;
        }

        return true;
    }

    protected string $view = 'filament.pages.official-letters';

    // حقول مذكرة الإيراد (تطلب رقم وتاريخ المذكرة)
    public ?int $revenue_fiscal_year_id = null;

    public ?int $revenue_month_id = null;

    public string $revenue_memo_number = '87';

    public string $revenue_memo_date = '';

    // حقول مذكرة الطاقة الإنتاجية (تطلب رقم وتاريخ المذكرة)
    public ?int $capacity_fiscal_year_id = null;

    public ?int $capacity_month_id = null;

    public string $capacity_memo_number = '88';

    public string $capacity_memo_date = '';

    // حقول موقف الحاويات (صادر مركزي - بدون رقم وتاريخ)
    public ?int $containers_fiscal_year_id = null;

    public ?int $containers_month_id = null;

    // حقول موقف البضائع (صادر مركزي - بدون رقم وتاريخ)
    public ?int $cargo_fiscal_year_id = null;

    public ?int $cargo_month_id = null;

    public function mount(): void
    {
        $currentFy = FiscalYear::where('is_current', true)->first() ?? FiscalYear::latest('year')->first();
        $defaultFyId = $currentFy?->id;

        // الأشهر الافتراضية بحسب توفر البيانات
        $monthAugust = Month::where('month_number', 8)->first() ?? Month::first();
        $monthSeptember = Month::where('month_number', 9)->first() ?? $monthAugust;

        $todayDate = date('d / m / Y');

        // الإيراد
        $this->revenue_fiscal_year_id = $defaultFyId;
        $this->revenue_month_id = $monthAugust?->id;
        $this->revenue_memo_number = '87';
        $this->revenue_memo_date = '9 / 9 / '.($currentFy?->year ?? date('Y'));

        // الطاقة
        $this->capacity_fiscal_year_id = $defaultFyId;
        $this->capacity_month_id = $monthAugust?->id;
        $this->capacity_memo_number = '88';
        $this->capacity_memo_date = '9 / 9 / '.($currentFy?->year ?? date('Y'));

        // الحاويات
        $this->containers_fiscal_year_id = $defaultFyId;
        $this->containers_month_id = $monthSeptember?->id;

        // البضائع
        $this->cargo_fiscal_year_id = $defaultFyId;
        $this->cargo_month_id = $monthAugust?->id;
    }

    public function getFiscalYearsProperty()
    {
        return FiscalYear::orderBy('year', 'desc')->get();
    }

    public function getMonthsProperty()
    {
        return Month::orderBy('month_number')->get();
    }
}
