<?php

namespace App\Services;

use App\Models\FiscalYear;
use App\Models\Month;
use App\Models\RevenueCenter;
use App\Models\RevenueRecord;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\IOFactory;

class SingleRevenueMonthlyImportService
{
    /**
     * خريطة الكلمات المفتاحية للمطابقة بين النصوص ومراكز الإيراد
     */
    protected static array $centerKeywords = [
        'north'      => ['شمالي', 'الشمالي', 'ام قصر الشمالي', 'أم قصر الشمالي', 'north'],
        'south'      => ['جنوبي', 'الجنوبي', 'ام قصر الجنوبي', 'أم قصر الجنوبي', 'south'],
        'khor'       => ['خور الزبير', 'خور', 'الزبير', 'khor', 'zubair'],
        'abu_flous'  => ['ابو فلوس', 'أبو فلوس', 'فلوس', 'abu flous', 'flous'],
        'maqil'      => ['معقل', 'المعقل', 'ميناء المعقل', 'maqil', 'al-maqil'],
        'bot'        => ['البصرة النفطي', 'ميناء البصرة النفطي', 'نفطي', 'بصرة نفطي', 'ملاحة', 'الملاحة', 'شؤون بحرية', 'الشؤون البحرية', 'bot', 'navigation'],
        'hq'         => ['مقر الشركة', 'المقر', 'المقر العام', 'مقر عام', 'تشكيلات اخرى', 'تشكيلات أخرى', 'ادارة عامة', 'headquarters', 'hq', 'other'],
    ];

    /**
     * استيراد إيراد شهر وسنة محددين (لمركز محدد أو لكافة المراكز السبعة)
     */
    public function import(?int $targetCenterId, int $fiscalYearId, int $monthId, string $filePath): array
    {
        $fiscalYear = FiscalYear::findOrFail($fiscalYearId);
        $month = Month::findOrFail($monthId);
        $allCenters = RevenueCenter::where('is_active', true)->orderBy('sort_order')->get();

        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);

        if (empty($rows)) {
            throw new \Exception('ملف الإكسل فارغ ولا يحتوي على بيانات.');
        }

        // استخراج بيانات المراكز من الملف
        $extractedCenters = $this->extractRevenueData($rows, $allCenters, $targetCenterId, $fiscalYearId, $monthId);

        if (empty($extractedCenters)) {
            throw new \Exception('لم يتم العثور على أرقام إيراد مطابقة في ملف الإكسل. يرجى التأكد من احتواء الملف على أعمدة الإيراد الكلي والصافي.');
        }

        $savedRecords = [];
        $totalGross = 0;
        $totalNet = 0;
        $userId = Auth::id() ?? 1;

        foreach ($extractedCenters as $centerId => $revData) {
            $gross = (float) ($revData['gross_revenue'] ?? 0);
            $net = (float) ($revData['net_revenue'] ?? 0);

            // التحقق المنطقي: لا يمكن أن يتجاوز الصافي الإيراد الكلي
            if ($net > $gross && $gross > 0) {
                $net = $gross;
            }

            $existing = RevenueRecord::withTrashed()->where([
                'revenue_center_id' => $centerId,
                'fiscal_year_id'    => $fiscalYearId,
                'month_id'          => $monthId,
            ])->first();

            $recordData = [
                'revenue_center_id' => $centerId,
                'fiscal_year_id'    => $fiscalYearId,
                'month_id'          => $monthId,
                'gross_revenue'     => $gross,
                'net_revenue'       => $net,
                'status'            => 'approved',
                'created_by'        => $userId,
            ];

            if ($existing) {
                if ($existing->trashed()) {
                    $existing->restore();
                }
                $existing->update($recordData);
                $savedRecords[] = $existing;
            } else {
                $savedRecords[] = RevenueRecord::create($recordData);
            }

            $totalGross += $gross;
            $totalNet += $net;
        }

        $centerName = $targetCenterId ? ($allCenters->firstWhere('id', $targetCenterId)?->name_ar ?? 'المركز المحدد') : 'كافة المراكز السبعة';

        ActivityLogger::log(
            'imported',
            "استيراد بيانات إيراد من Excel لـ ({$centerName}) - شهر ({$month->name_ar}) سنة ({$fiscalYear->year})",
            RevenueRecord::class,
            $savedRecords[0]->id ?? null,
            [
                'target_center_id' => $targetCenterId,
                'total_gross'      => $totalGross,
                'total_net'        => $totalNet,
                'records_count'    => count($savedRecords),
            ]
        );

        return [
            'status'         => true,
            'center_name'    => $centerName,
            'month_name'     => $month->name_ar,
            'year'           => $fiscalYear->year,
            'records_count'  => count($savedRecords),
            'total_gross'    => $totalGross,
            'total_net'      => $totalNet,
            'saved_records'  => $savedRecords,
        ];
    }

    /**
     * استخراج بيانات الإيراد من صفوف الإكسل
     */
    protected function extractRevenueData(array $rows, $allCenters, ?int $targetCenterId, int $fiscalYearId, int $monthId): array
    {
        $result = [];

        // 1. فحص إذا كان الملف جدولاً أفقياً بترويسة (Header Row)
        $headerRowIdx = null;
        $centerCol = null;
        $grossCol = null;
        $netCol = null;
        $fyCol = null;
        $mCol = null;

        foreach ($rows as $rIdx => $row) {
            foreach ($row as $colLetter => $cellValue) {
                if ($cellValue === null) continue;
                $norm = $this->normalizeString((string) $cellValue);

                if ($centerCol === null && ($this->matches($norm, ['revenue_center_id', 'مركز الايراد', 'التشكيل', 'المركز', 'اسم المركز', 'الميناء']))) {
                    $centerCol = $colLetter;
                }
                if ($grossCol === null && ($this->matches($norm, ['gross_revenue', 'الايراد الكلي', 'الإيراد الكلي', 'الكلي', 'الايراد الاجمالي', 'gross']))) {
                    $grossCol = $colLetter;
                }
                if ($netCol === null && ($this->matches($norm, ['net_revenue', 'الايراد الصافي', 'الإيراد الصافي', 'الصافي', 'net']))) {
                    $netCol = $colLetter;
                }
                if ($fyCol === null && ($this->matches($norm, ['fiscal_year_id', 'السنة المالية', 'السنة', 'عام']))) {
                    $fyCol = $colLetter;
                }
                if ($mCol === null && ($this->matches($norm, ['month_id', 'الشهر', 'شهر']))) {
                    $mCol = $colLetter;
                }
            }

            if ($grossCol !== null || ($centerCol !== null && $netCol !== null)) {
                $headerRowIdx = $rIdx;
                break;
            }
        }

        // إذا وجدنا جدولاً أفقياً برؤوس أعمدة
        if ($headerRowIdx !== null && $grossCol !== null) {
            foreach ($rows as $rIdx => $row) {
                if ($rIdx <= $headerRowIdx) continue;

                // فحص السنة والشهر إذا كانا موجودين في أعمدة الصف
                if ($fyCol !== null && !empty($row[$fyCol])) {
                    $rowFy = (int) $this->cleanNumber($row[$fyCol]);
                    if ($rowFy > 0 && $rowFy !== $fiscalYearId && $rowFy !== (int) FiscalYear::find($fiscalYearId)?->year) {
                        continue;
                    }
                }
                if ($mCol !== null && !empty($row[$mCol])) {
                    $rowM = (int) $this->cleanNumber($row[$mCol]);
                    if ($rowM > 0 && $rowM !== $monthId && $rowM !== (int) Month::find($monthId)?->month_number) {
                        continue;
                    }
                }

                $gross = $this->cleanNumber($row[$grossCol] ?? 0);
                $net = $netCol !== null ? $this->cleanNumber($row[$netCol] ?? 0) : 0;

                // إذا كان المستخدم حدد مركزاً واحداً في النموذج، والملف يحتوي على صف واحد فقط أو هذا المركز
                if ($targetCenterId) {
                    if ($centerCol !== null && !empty($row[$centerCol])) {
                        $matchedId = $this->resolveCenterId((string)$row[$centerCol], $allCenters);
                        if ($matchedId !== $targetCenterId) {
                            continue;
                        }
                    }
                    $result[$targetCenterId] = [
                        'gross_revenue' => $gross,
                        'net_revenue'   => $net,
                    ];
                    break;
                }

                // استيراد لكافة المراكز من الجدول
                if ($centerCol !== null && !empty($row[$centerCol])) {
                    $matchedId = $this->resolveCenterId((string)$row[$centerCol], $allCenters);
                    if ($matchedId) {
                        $result[$matchedId] = [
                            'gross_revenue' => $gross,
                            'net_revenue'   => $net,
                        ];
                    }
                }
            }

            if (!empty($result)) {
                return $result;
            }
        }

        // 2. فحص نمط الجداول الرأسية / القائمة (Vertical / Key-Value List)
        // مثلاً: اسم المركز في العمود الأول، والإيراد في العمود الثاني
        foreach ($rows as $row) {
            $cells = array_values(array_filter($row, fn($c) => $c !== null && trim((string)$c) !== ''));
            if (count($cells) >= 2) {
                $firstCell = (string)$cells[0];
                $secondCell = $cells[1];
                $thirdCell = $cells[2] ?? null;

                $matchedId = $this->resolveCenterId($firstCell, $allCenters);
                if ($matchedId) {
                    if ($targetCenterId && $matchedId !== $targetCenterId) {
                        continue;
                    }
                    $gross = $this->cleanNumber($secondCell);
                    $net   = $thirdCell !== null ? $this->cleanNumber($thirdCell) : 0;

                    $result[$matchedId] = [
                        'gross_revenue' => $gross,
                        'net_revenue'   => $net,
                    ];
                }
            }
        }

        // إذا كان الاستيراد لمركز محدد مسبقاً، ولم نجد سوى أرقام كلي وصافي عامة بالملف
        if ($targetCenterId && empty($result)) {
            $foundGross = 0;
            $foundNet = 0;
            foreach ($rows as $row) {
                $cells = array_values(array_filter($row, fn($c) => $c !== null && trim((string)$c) !== ''));
                if (count($cells) >= 2) {
                    $lbl = $this->normalizeString((string)$cells[0]);
                    if ($this->matches($lbl, ['كلي', 'gross', 'اجمالي', 'الايراد'])) {
                        $foundGross = $this->cleanNumber($cells[1]);
                    }
                    if ($this->matches($lbl, ['صافي', 'net', 'الصافي'])) {
                        $foundNet = $this->cleanNumber($cells[1]);
                    }
                }
            }
            if ($foundGross > 0 || $foundNet > 0) {
                $result[$targetCenterId] = [
                    'gross_revenue' => $foundGross,
                    'net_revenue'   => $foundNet,
                ];
            }
        }

        return $result;
    }

    /**
     * مطابقة النص مع مركز الإيراد المناسب
     */
    protected function resolveCenterId(string $text, $allCenters): ?int
    {
        $clean = trim($text);
        if (is_numeric($clean)) {
            $id = (int)$clean;
            if ($allCenters->contains('id', $id)) {
                return $id;
            }
        }

        $norm = $this->normalizeString($text);

        foreach ($allCenters as $center) {
            $centerNorm = $this->normalizeString($center->name_ar);
            if (str_contains($norm, $centerNorm) || str_contains($centerNorm, $norm)) {
                return $center->id;
            }
        }

        // مطابقة بالكلمات المفتاحية
        foreach (static::$centerKeywords as $key => $keywords) {
            if ($this->matches($norm, $keywords)) {
                $matchedCenter = match ($key) {
                    'north'      => $allCenters->first(fn($c) => str_contains($c->name_ar, 'الشمالي')),
                    'south'      => $allCenters->first(fn($c) => str_contains($c->name_ar, 'الجنوبي')),
                    'khor'       => $allCenters->first(fn($c) => str_contains($c->name_ar, 'خور')),
                    'abu_flous'  => $allCenters->first(fn($c) => str_contains($c->name_ar, 'فلوس')),
                    'maqil'      => $allCenters->first(fn($c) => str_contains($c->name_ar, 'المعقل') || str_contains($c->name_ar, 'معقل')),
                    'bot'        => $allCenters->first(fn($c) => str_contains($c->name_ar, 'النفطي') || str_contains($c->name_ar, 'الملاحة') || str_contains($c->name_ar, 'بحرية')),
                    'hq'         => $allCenters->first(fn($c) => str_contains($c->name_ar, 'المقر') || str_contains($c->name_ar, 'مقر') || str_contains($c->name_ar, 'أخرى') || str_contains($c->name_ar, 'اخرى')),
                    default      => null,
                };
                if ($matchedCenter) {
                    return $matchedCenter->id;
                }
            }
        }

        return null;
    }

    /**
     * تنظيف الأرقام
     */
    protected function cleanNumber(mixed $val): float
    {
        if (is_numeric($val)) {
            return (float) $val;
        }

        if (is_string($val)) {
            $clean = str_replace([',', ' ', 'د.ع', 'دينار', '$'], '', trim($val));
            return is_numeric($clean) ? (float) $clean : 0.0;
        }

        return 0.0;
    }

    /**
     * تسوية النص للمطابقة
     */
    protected function normalizeString(string $str): string
    {
        $str = mb_strtolower(trim($str));
        $str = str_replace(['أ', 'إ', 'آ'], 'ا', $str);
        $str = str_replace('ة', 'ه', $str);
        $str = str_replace('ى', 'ي', $str);
        $str = preg_replace('/[_\-\(\)\/]/u', ' ', $str);
        $str = preg_replace('/\s+/', ' ', $str);
        return trim($str);
    }

    /**
     * فحص مطابقة أي من الكلمات
     */
    protected function matches(string $norm, array $keywords): bool
    {
        foreach ($keywords as $kw) {
            $kwNorm = $this->normalizeString($kw);
            if (str_contains($norm, $kwNorm) || str_contains($kwNorm, $norm)) {
                return true;
            }
        }
        return false;
    }
}
