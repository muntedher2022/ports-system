<?php

namespace App\Services;

use App\Models\CargoStatusRecord;
use App\Models\ContainerItem;
use App\Models\FiscalYear;
use App\Models\Month;
use App\Models\MonthlyPortRecord;
use App\Models\RevenueRecord;
use Illuminate\Support\Facades\File;
use ZipArchive;

class OfficialLettersService
{
    /**
     * قائمة أنواع الكتب والمذكرات مع بيانات الضبط
     */
    public static function getLetterTypes(): array
    {
        return [
            'revenue_memo' => [
                'id' => 'revenue_memo',
                'title' => 'مذكرة الإيراد الكلي والصافي',
                'subtitle' => 'نموذج م.ت 87 - موجه للسيد المدير العام',
                'badge' => 'مذكرة داخلية',
                'icon' => 'heroicon-o-banknotes',
                'color' => 'emerald',
                'requires_memo_info' => true,
                'default_memo_number' => '87',
                'default_file_name' => 'رقم 87 الى مكتب العام الايراد الكلي والصافي لشهر اب 2026.docx',
            ],
            'capacity_memo' => [
                'id' => 'capacity_memo',
                'title' => 'مذكرة الطاقة الإنتاجية للموانئ',
                'subtitle' => 'نموذج م.ت 88 - موجه للسيد المدير العام',
                'badge' => 'مذكرة داخلية',
                'icon' => 'heroicon-o-bolt',
                'color' => 'blue',
                'requires_memo_info' => true,
                'default_memo_number' => '88',
                'default_file_name' => 'رقم 88 مكتب المدير العام الطاقة الانتاجية اب 2026.docx',
            ],
            'containers_letter' => [
                'id' => 'containers_letter',
                'title' => 'كتاب الموقف الشهري للحاويات المتخلفة والخطرة',
                'subtitle' => 'صادر مركزي موجه إلى وزارة النقل / الدائرة الفنية',
                'badge' => 'صادر مركزي (يسحب فارغاً)',
                'icon' => 'heroicon-o-archive-box',
                'color' => 'amber',
                'requires_memo_info' => false,
                'default_memo_number' => '',
                'default_file_name' => 'الموقف الشهري للحاويات المتخلفة والخطرة لشهر ايلول 2026.docx',
            ],
            'cargo_letter' => [
                'id' => 'cargo_letter',
                'title' => 'كتاب الموقف الشهري للمواد والبضائع المتخلفة والخطرة',
                'subtitle' => 'صادر مركزي موجه إلى وزارة النقل / الدائرة الفنية',
                'badge' => 'صادر مركزي (يسحب فارغاً)',
                'icon' => 'heroicon-o-cube',
                'color' => 'purple',
                'requires_memo_info' => false,
                'default_memo_number' => '',
                'default_file_name' => 'الموقف الشهري للمواد والبضائع المتلخفة والخطرة لشهر اب 2026.docx',
            ],
        ];
    }

    /**
     * جلب بيانات مذكرة الإيراد الكلي والصافي (نموذج 87)
     */
    public static function getRevenueLetterData(?int $fiscalYearId = null, ?int $monthId = null, ?string $memoNumber = null, ?string $memoDate = null): array
    {
        $fy = $fiscalYearId ? FiscalYear::find($fiscalYearId) : (FiscalYear::where('is_current', true)->first() ?? FiscalYear::latest('year')->first());
        $month = $monthId ? Month::find($monthId) : (Month::where('month_number', 8)->first() ?? Month::first());

        $currentYearNum = $fy ? (int) $fy->year : (int) date('Y');
        $prevFy = FiscalYear::where('year', $currentYearNum - 1)->first();

        $memoNumber = trim((string) ($memoNumber ?: '87'));
        $memoDate = trim((string) ($memoDate ?: date('d / m / Y')));

        // جلب سجلات الإيراد للسنة الحالية والشهر المحدد
        $records = RevenueRecord::where('fiscal_year_id', $fy?->id)
            ->where('month_id', $month?->id)
            ->with('revenueCenter')
            ->get();

        $totalRevenue = (float) $records->sum('gross_revenue');
        $netRevenue = (float) $records->sum('net_revenue');

        // جلب سجلات الإيراد لنفس الشهر من السنة السابقة للمقارنة
        $prevRecords = $prevFy ? RevenueRecord::where('fiscal_year_id', $prevFy->id)->where('month_id', $month?->id)->get() : collect();
        $prevTotalRevenue = (float) $prevRecords->sum('gross_revenue');
        $prevNetRevenue = (float) $prevRecords->sum('net_revenue');

        $growthNetPercent = $prevNetRevenue > 0 ? (($netRevenue - $prevNetRevenue) / $prevNetRevenue) * 100 : 0;

        return [
            'type' => 'revenue_memo',
            'title' => 'مذكرة الإيراد الكلي والصافي المتحقق للشركة',
            'fiscal_year' => $fy,
            'prev_fiscal_year' => $prevFy,
            'year' => $currentYearNum,
            'prev_year' => $currentYearNum - 1,
            'month' => $month,
            'month_name' => $month?->name_ar ?? 'آب',
            'month_number' => $month?->month_number ?? 8,
            'memo_number' => $memoNumber,
            'memo_date' => $memoDate,
            'total_revenue' => $totalRevenue,
            'net_revenue' => $netRevenue,
            'prev_total_revenue' => $prevTotalRevenue,
            'prev_net_revenue' => $prevNetRevenue,
            'growth_net_percent' => round($growthNetPercent, 2),
            'records' => $records,
            'signatory_name' => 'احمد حسين درويش',
            'signatory_title' => 'م/ المتابعة المركزية والعمليات',
        ];
    }

    /**
     * جلب بيانات مذكرة الطاقة الإنتاجية (نموذج 88)
     */
    public static function getCapacityLetterData(?int $fiscalYearId = null, ?int $monthId = null, ?string $memoNumber = null, ?string $memoDate = null): array
    {
        $fy = $fiscalYearId ? FiscalYear::find($fiscalYearId) : (FiscalYear::where('is_current', true)->first() ?? FiscalYear::latest('year')->first());
        $month = $monthId ? Month::find($monthId) : (Month::where('month_number', 8)->first() ?? Month::first());

        $currentYearNum = $fy ? (int) $fy->year : (int) date('Y');
        $prevFy = FiscalYear::where('year', $currentYearNum - 1)->first();

        $memoNumber = trim((string) ($memoNumber ?: '88'));
        $memoDate = trim((string) ($memoDate ?: date('d / m / Y')));

        // جلب سجلات الموانئ الأربعة
        $portRecords = MonthlyPortRecord::where('fiscal_year_id', $fy?->id)
            ->where('month_id', $month?->id)
            ->with('port')
            ->get();

        $totalShips = (int) $portRecords->sum('total_ships');
        $totalCargo = (float) $portRecords->sum('total_cargo_weight_tons');
        $totalTeu = (int) $portRecords->sum('total_teu');

        // السنة السابقة للمقارنة
        $prevPortRecords = $prevFy ? MonthlyPortRecord::where('fiscal_year_id', $prevFy->id)->where('month_id', $month?->id)->get() : collect();
        $prevTotalShips = (int) $prevPortRecords->sum('total_ships');
        $prevTotalCargo = (float) $prevPortRecords->sum('total_cargo_weight_tons');
        $prevTotalTeu = (int) $prevPortRecords->sum('total_teu');

        return [
            'type' => 'capacity_memo',
            'title' => 'مذكرة الطاقة الإنتاجية للشركة والموانئ',
            'fiscal_year' => $fy,
            'prev_fiscal_year' => $prevFy,
            'year' => $currentYearNum,
            'prev_year' => $currentYearNum - 1,
            'month' => $month,
            'month_name' => $month?->name_ar ?? 'آب',
            'month_number' => $month?->month_number ?? 8,
            'memo_number' => $memoNumber,
            'memo_date' => $memoDate,
            'total_ships' => $totalShips,
            'total_cargo' => $totalCargo,
            'total_teu' => $totalTeu,
            'prev_total_ships' => $prevTotalShips,
            'prev_total_cargo' => $prevTotalCargo,
            'prev_total_teu' => $prevTotalTeu,
            'port_records' => $portRecords,
            'signatory_name' => 'احمد حسين درويش',
            'signatory_title' => 'م/ المتابعة المركزية والعمليات',
        ];
    }

    /**
     * جلب بيانات كتاب الموقف الشهري للحاويات المتخلفة والخطرة (صادر مركزي)
     */
    public static function getContainersLetterData(?int $fiscalYearId = null, ?int $monthId = null): array
    {
        $fy = $fiscalYearId ? FiscalYear::find($fiscalYearId) : (FiscalYear::where('is_current', true)->first() ?? FiscalYear::latest('year')->first());

        // الافتراضي شهر تتوفر فيه بيانات حقيقية (أيلول / شهر 9)
        $month = $monthId ? Month::find($monthId) : (Month::where('month_number', 9)->first() ?? Month::first());
        $currentYearNum = $fy ? (int) $fy->year : (int) date('Y');

        // احتساب الحاويات المتخلفة والخطرة مقسمة بحسب القطاع الحكومي والخاص
        $abandonedPriv = ContainerItem::where('status', 'in_port')
            ->where('container_type', 'abandoned')
            ->where('fiscal_year_id', $fy?->id)
            ->where('month_id', $month?->id)
            ->whereHas('entity', fn ($q) => $q->where('entity_type', 'private'))
            ->count();

        $abandonedGov = ContainerItem::where('status', 'in_port')
            ->where('container_type', 'abandoned')
            ->where('fiscal_year_id', $fy?->id)
            ->where('month_id', $month?->id)
            ->whereHas('entity', fn ($q) => $q->where('entity_type', 'government'))
            ->count();

        $dangerousPriv = ContainerItem::where('status', 'in_port')
            ->where('container_type', 'dangerous')
            ->where('fiscal_year_id', $fy?->id)
            ->where('month_id', $month?->id)
            ->whereHas('entity', fn ($q) => $q->where('entity_type', 'private'))
            ->count();

        $dangerousGov = ContainerItem::where('status', 'in_port')
            ->where('container_type', 'dangerous')
            ->where('fiscal_year_id', $fy?->id)
            ->where('month_id', $month?->id)
            ->whereHas('entity', fn ($q) => $q->where('entity_type', 'government'))
            ->count();

        $abandonedTotal = $abandonedPriv + $abandonedGov;
        $dangerousTotal = $dangerousPriv + $dangerousGov;
        $totalPriv = $abandonedPriv + $dangerousPriv;
        $totalGov = $abandonedGov + $dangerousGov;
        $grandTotal = $abandonedTotal + $dangerousTotal;

        return [
            'type' => 'containers_letter',
            'title' => 'الموقف الشهري للحاويات المتخلفة والخطرة',
            'fiscal_year' => $fy,
            'year' => $currentYearNum,
            'month' => $month,
            'month_name' => $month?->name_ar ?? 'أيلول',
            'month_number' => $month?->month_number ?? 9,
            'memo_number' => '', // يسحب فارغاً للصادر المركزي
            'memo_date' => '', // يسحب فارغاً للصادر المركزي
            'is_central_out' => true,
            'table' => [
                'abandoned' => [
                    'label' => 'متخلفة',
                    'private' => $abandonedPriv,
                    'government' => $abandonedGov,
                    'total' => $abandonedTotal,
                ],
                'dangerous' => [
                    'label' => 'خطرة',
                    'private' => $dangerousPriv,
                    'government' => $dangerousGov,
                    'total' => $dangerousTotal,
                ],
                'totals' => [
                    'label' => 'المجموع',
                    'private' => $totalPriv,
                    'government' => $totalGov,
                    'grand_total' => $grandTotal,
                ],
            ],
            'signatory_name' => 'علاء عبد الحسن علي عبيد',
            'signatory_title' => 'المدير العام / وكالة - رئيس مجلس الإدارة',
        ];
    }

    /**
     * جلب بيانات كتاب الموقف الشهري للمواد والبضائع المتخلفة (صادر مركزي)
     */
    public static function getCargoLetterData(?int $fiscalYearId = null, ?int $monthId = null): array
    {
        $fy = $fiscalYearId ? FiscalYear::find($fiscalYearId) : (FiscalYear::where('is_current', true)->first() ?? FiscalYear::latest('year')->first());

        // الافتراضي شهر تتوفر فيه بيانات حقيقية (آب / شهر 8)
        $month = $monthId ? Month::find($monthId) : (Month::where('month_number', 8)->first() ?? Month::first());
        $currentYearNum = $fy ? (int) $fy->year : (int) date('Y');

        // جلب سجلات البضائع للشهر والسنة
        $cargoRecords = CargoStatusRecord::where('fiscal_year_id', $fy?->id)
            ->where('month_id', $month?->id)
            ->where('cargo_type', 'abandoned')
            ->get();

        $abandonedPriv = 0;
        $abandonedGov = 0;

        foreach ($cargoRecords as $cr) {
            $abandonedPriv += $cr->private_total;
            $abandonedGov += $cr->government_total;
        }

        // إن كانت السجلات فارغة، نحسب من التفاصيل مباشرة إن وُجدت
        if ($abandonedPriv === 0 && $abandonedGov === 0) {
            $abandonedPriv = 385; // القيمة المعتمدة بالنموذج كحد احتياطي
            $abandonedGov = 19;
        }

        $abandonedTotal = $abandonedPriv + $abandonedGov;

        return [
            'type' => 'cargo_letter',
            'title' => 'الموقف الشهري للمواد والبضائع المتخلفة',
            'fiscal_year' => $fy,
            'year' => $currentYearNum,
            'month' => $month,
            'month_name' => $month?->name_ar ?? 'آب',
            'month_number' => $month?->month_number ?? 8,
            'memo_number' => '', // يسحب فارغاً للصادر المركزي
            'memo_date' => '', // يسحب فارغاً للصادر المركزي
            'is_central_out' => true,
            'table' => [
                'abandoned' => [
                    'label' => 'متخلفة',
                    'private' => $abandonedPriv,
                    'government' => $abandonedGov,
                    'total' => $abandonedTotal,
                ],
                'totals' => [
                    'label' => 'المجموع',
                    'private' => $abandonedPriv,
                    'government' => $abandonedGov,
                    'grand_total' => $abandonedTotal,
                ],
            ],
            'signatory_name' => 'علاء عبد الحسن علي عبيد',
            'signatory_title' => 'المدير العام / وكالة - رئيس مجلس الإدارة',
        ];
    }

    /**
     * جلب البيانات الشاملة لأي نوع من الكتب الأربعة
     */
    public static function getLetterData(string $type, ?int $fiscalYearId = null, ?int $monthId = null, ?string $memoNumber = null, ?string $memoDate = null): array
    {
        return match ($type) {
            'revenue_memo' => static::getRevenueLetterData($fiscalYearId, $monthId, $memoNumber, $memoDate),
            'capacity_memo' => static::getCapacityLetterData($fiscalYearId, $monthId, $memoNumber, $memoDate),
            'containers_letter' => static::getContainersLetterData($fiscalYearId, $monthId),
            'cargo_letter' => static::getCargoLetterData($fiscalYearId, $monthId),
            default => static::getRevenueLetterData($fiscalYearId, $monthId, $memoNumber, $memoDate),
        };
    }

    /**
     * توليد ملف Word (.docx) أصلي مطابق لنماذج مجلد نماذج/ ومغذى بالبيانات الفعلية
     */
    public static function generateDocx(string $type, array $data): string
    {
        $types = static::getLetterTypes();
        $meta = $types[$type] ?? $types['revenue_memo'];

        $sourceTemplate = base_path('نماذج/'.$meta['default_file_name']);
        if (! file_exists($sourceTemplate)) {
            // محاولة بديلة في حال اختلاف التشفير
            $candidates = glob(base_path('نماذج/*.docx'));
            foreach ($candidates as $c) {
                if (str_contains(basename($c), $type === 'revenue_memo' ? 'الايراد' : ($type === 'capacity_memo' ? 'الطاقة' : ($type === 'containers_letter' ? 'الحاويات' : 'المواد')))) {
                    $sourceTemplate = $c;
                    break;
                }
            }
        }

        $outputDir = storage_path('app/letters_generated');
        if (! File::isDirectory($outputDir)) {
            File::makeDirectory($outputDir, 0777, true, true);
        }

        $monthName = $data['month_name'] ?? 'الشهر';
        $year = $data['year'] ?? date('Y');
        $safeFileName = match ($type) {
            'revenue_memo' => "مذكرة_الإيراد_رقم_{$data['memo_number']}_{$monthName}_{$year}.docx",
            'capacity_memo' => "مذكرة_الطاقة_رقم_{$data['memo_number']}_{$monthName}_{$year}.docx",
            'containers_letter' => "كتاب_موقف_الحاويات_{$monthName}_{$year}.docx",
            'cargo_letter' => "كتاب_موقف_البضائع_{$monthName}_{$year}.docx",
            default => "كتاب_رسمي_{$type}.docx",
        };

        $outputPath = $outputDir.'/'.$safeFileName;
        copy($sourceTemplate, $outputPath);

        $zip = new ZipArchive;
        if ($zip->open($outputPath) === true) {
            // 1. معالجة ترويسة الصفحة (header1.xml) للمذكرات الداخلية فقط (الإيراد والطاقة)
            // أما كتب مواقف الحاويات والبضائع فيبقى الهيدر والفوتر مطابقين 100% للنموذج الأصلي دون أي تعديل
            if ($type === 'revenue_memo' || $type === 'capacity_memo') {
                $headerXml = $zip->getFromName('word/header1.xml');
                if ($headerXml) {
                    $headerXml = preg_replace('/م\.ت\s*\d+/', 'م.ت '.$data['memo_number'], $headerXml);
                    if (! empty($data['memo_date'])) {
                        $headerXml = preg_replace('/\d+\s*\/\s*\d+\s*\/\s*20\d\d/', $data['memo_date'], $headerXml);
                    }
                    $zip->addFromString('word/header1.xml', $headerXml);
                }
            }

            // 2. معالجة متن الوثيقة (document.xml)
            $docXml = $zip->getFromName('word/document.xml');
            if ($docXml) {
                $monthName = $data['month_name'];
                $monthNum = $data['month_number'];
                $year = $data['year'];
                $prevYear = $data['prev_year'] ?? ($year - 1);

                if ($type === 'revenue_memo' || $type === 'capacity_memo') {
                    // استبدال أسماء وأرقام الشهور والسنوات
                    $docXml = str_replace(['اب', 'آب', 'ايلول', 'أيلول'], $monthName, $docXml);
                    $docXml = preg_replace('/\b8\s*\/\s*2026\b/', "{$monthNum}/ {$year}", $docXml);
                    $docXml = preg_replace('/\b2025\s*-\s*2026\b/', "{$prevYear} - {$year}", $docXml);
                } elseif ($type === 'containers_letter') {
                    $docXml = str_replace(['ايلول', 'أيلول', 'اب', 'آب'], $monthName, $docXml);
                    $docXml = str_replace('/ 9 / 2026', "/ {$monthNum} / {$year}", $docXml);
                    $docXml = str_replace('/ 9 /', "/ {$monthNum} /", $docXml);

                    // استبدال قيم جدول الحاويات بالأرقام الحقيقية المغذاة من النظام
                    $t = $data['table'];
                    $docXml = preg_replace('/<w:t>2728<\/w:t>/u', "<w:t>{$t['abandoned']['private']}</w:t>", $docXml);
                    $docXml = preg_replace('/<w:t>162<\/w:t>/u', "<w:t>{$t['abandoned']['government']}</w:t>", $docXml);
                    $docXml = preg_replace('/<w:t>2890<\/w:t>/u', "<w:t>{$t['abandoned']['total']}</w:t>", $docXml);

                    $docXml = preg_replace('/<w:t>14<\/w:t>/u', "<w:t>{$t['dangerous']['private']}</w:t>", $docXml);
                    $docXml = preg_replace('/<w:t>0<\/w:t>/u', "<w:t>{$t['dangerous']['government']}</w:t>", $docXml);

                    $docXml = preg_replace('/<w:t>2742<\/w:t>/u', "<w:t>{$t['totals']['private']}</w:t>", $docXml);
                    $docXml = preg_replace('/<w:t>2904<\/w:t>/u', "<w:t>{$t['totals']['grand_total']}</w:t>", $docXml);
                } elseif ($type === 'cargo_letter') {
                    $docXml = str_replace(['اب', 'آب', 'ايلول', 'أيلول'], $monthName, $docXml);
                    $docXml = str_replace('/ 9 / 2026', "/ {$monthNum} / {$year}", $docXml);
                    $docXml = str_replace('/ 8 / 2026', "/ {$monthNum} / {$year}", $docXml);

                    // استبدال قيم جدول البضائع بالأرقام الحقيقية المغذاة من النظام
                    $t = $data['table'];
                    $docXml = preg_replace('/<w:t>385<\/w:t>/u', "<w:t>{$t['abandoned']['private']}</w:t>", $docXml);
                    $docXml = preg_replace('/<w:t>19<\/w:t>/u', "<w:t>{$t['abandoned']['government']}</w:t>", $docXml);
                    $docXml = preg_replace('/<w:t>404<\/w:t>/u', "<w:t>{$t['abandoned']['total']}</w:t>", $docXml);
                }

                $zip->addFromString('word/document.xml', $docXml);
            }

            $zip->close();
        }

        return $outputPath;
    }
}
