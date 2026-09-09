<?php

namespace App\Filament\Pages;

use App\Enums\NavigationGroup;
use App\Models\FiscalYear;
use App\Models\Port;
use App\Services\PortAnalyticsService;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;

class StandardDeviationAnalytics extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedCalculator;
    protected static ?string $navigationLabel = 'تحليل الانحراف المعياري والاستقرار';
    protected static ?string $title = 'تحليل الانحراف المعياري واستقرار الأداء التشغيلي والمالي';
    protected static string|\UnitEnum|null $navigationGroup = NavigationGroup::Analytics;
    protected static ?int $navigationSort = 6;

    public static function canAccess(): bool
    {
        $user = Auth::user();
        if (! $user) return false;

        if ($user->hasRole(['super_admin', 'المدير العام', 'general_manager']) || $user->user_type === 'general_manager') {
            return true;
        }

        return $user->can('View:StandardDeviationAnalytics')
            || $user->hasRole(['مسؤول المتابعة المركزية والعمليات', 'operations_manager', 'مسؤول الإيراد المالي', 'finance_manager'])
            || in_array($user->user_type, ['general_manager', 'operations_manager', 'finance_manager']);
    }

    protected string $view = 'filament.pages.standard-deviation-analytics';

    public ?int $selectedFiscalYearId = null;
    public ?int $selectedPortId = null; // null = عموم الموانئ
    public string $selectedMetric = 'tonnage'; // 'tonnage', 'revenue', 'ships', 'teu'

    public function mount(): void
    {
        $this->selectedFiscalYearId = FiscalYear::where('is_current', true)->first()?->id
            ?? FiscalYear::orderBy('year', 'desc')->first()?->id;
    }

    public function getDataProperty(): array
    {
        $fiscalYears = FiscalYear::orderBy('year', 'desc')->get();
        $ports = Port::where('is_active', true)->whereHas('monthlyPortRecords')->orderBy('sort_order')->get();
        $service = app(PortAnalyticsService::class);

        $currentYearStats = $service->getYearlyStandardDeviation(
            (int) $this->selectedFiscalYearId,
            $this->selectedPortId
        );

        // مقارنة الانحراف المعياري والاستقرار عبر كافة السنوات
        $multiYearStats = [];
        foreach ($fiscalYears as $year) {
            $yStats = $service->getYearlyStandardDeviation($year->id, $this->selectedPortId);
            $multiYearStats[$year->year] = $yStats;
        }

        // تفاصيل المؤشر المختار للشهر
        $metricKey = $this->selectedMetric;
        $activeStat = $currentYearStats[$metricKey] ?? [];
        $mean = $activeStat['mean'] ?? 0;
        $stdDev = $activeStat['std_dev'] ?? 1;

        $monthlyRows = [];
        foreach ($currentYearStats['monthly'] as $mNum => $mData) {
            $val = (float) ($mData[$metricKey] ?? 0);
            if ($val > 0) {
                $dev = $val - $mean;
                $zScore = $stdDev > 0 ? ($dev / $stdDev) : 0;

                $assessment = match(true) {
                    $zScore >= 1.5  => 'طفرة إنتاجية استثنائية (أعلى من المعدل بشدة)',
                    $zScore >= 0.5  => 'نشاط مرتفع (فوق المتوسط)',
                    $zScore >= -0.5 => 'ضمن النطاق الطبيعي المستقر (المثالي)',
                    $zScore >= -1.5 => 'نشاط منخفض (تحت المتوسط)',
                    default         => 'انخفاض حاد غير معتاد (تراجع استثنائي)',
                };

                $assessmentType = match(true) {
                    $zScore >= 0.5  => 'high',
                    $zScore >= -0.5 => 'normal',
                    default         => 'low',
                };
            } else {
                $dev = 0;
                $zScore = 0;
                $assessment = 'لا توجد بيانات مسجلة للشهر';
                $assessmentType = 'none';
            }

            $monthlyRows[] = [
                'month_name'      => $mData['month_name'],
                'val'             => $val,
                'deviation'       => $dev,
                'z_score'         => round($zScore, 2),
                'assessment'      => $assessment,
                'assessment_type' => $assessmentType,
            ];
        }

        return [
            'fiscalYears'      => $fiscalYears,
            'ports'            => $ports,
            'currentYearStats' => $currentYearStats,
            'activeStat'       => $activeStat,
            'monthlyRows'      => $monthlyRows,
            'multiYearStats'   => $multiYearStats,
        ];
    }
}
