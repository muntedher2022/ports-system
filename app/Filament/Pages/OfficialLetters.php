<?php

namespace App\Filament\Pages;

use App\Enums\NavigationGroup;
use App\Models\FiscalYear;
use App\Models\Month;
use App\Models\Setting;
use Filament\Notifications\Notification;
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

    // حقول موقف الحاويات (صادر مركزي)
    public ?int $containers_fiscal_year_id = null;

    public ?int $containers_month_id = null;

    public string $containers_signature_date = '';

    // حقول موقف البضائع (صادر مركزي)
    public ?int $cargo_fiscal_year_id = null;

    public ?int $cargo_month_id = null;

    public string $cargo_signature_date = '';

    // إعدادات الموقعين الرسميين (ديناميكية)
    public string $general_manager_name = '';

    public string $general_manager_title = '';

    public string $central_followup_officer_name = '';

    public string $central_followup_officer_title = '';

    public function mount(): void
    {
        $currentFy = FiscalYear::where('is_current', true)->first() ?? FiscalYear::latest('year')->first();
        $defaultFyId = $currentFy?->id;

        // الأشهر الافتراضية بحسب توفر البيانات
        $monthAugust = Month::where('month_number', 8)->first() ?? Month::first();
        $monthSeptember = Month::where('month_number', 9)->first() ?? $monthAugust;

        $todayDate = date('d/m/Y');

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
        $this->containers_signature_date = $todayDate;

        // البضائع
        $this->cargo_fiscal_year_id = $defaultFyId;
        $this->cargo_month_id = $monthAugust?->id;
        $this->cargo_signature_date = $todayDate;

        // تحميل بيانات الموقعين الرسميين من قاعدة البيانات
        $this->general_manager_name = Setting::get('general_manager_name', 'عبد الله عاتي عبد الله');
        $this->general_manager_title = Setting::get('general_manager_title', 'المدير العام / وكالة - رئيس مجلس الإدارة');
        $this->central_followup_officer_name = Setting::get('central_followup_officer_name', 'احمد حسين درويش');
        $this->central_followup_officer_title = Setting::get('central_followup_officer_title', 'م/ المتابعة المركزية والعمليات');
    }

    /**
     * حفظ أسماء وصفات الموقعين الرسميين فوراً
     */
    public function saveSignatories(): void
    {
        Setting::set('general_manager_name', trim($this->general_manager_name), 'official_letters');
        Setting::set('general_manager_title', trim($this->general_manager_title), 'official_letters');
        Setting::set('central_followup_officer_name', trim($this->central_followup_officer_name), 'official_letters');
        Setting::set('central_followup_officer_title', trim($this->central_followup_officer_title), 'official_letters');

        Notification::make()
            ->title('تم حفظ وتحديث بيانات التواقيع الرسمية بنجاح')
            ->success()
            ->send();
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
