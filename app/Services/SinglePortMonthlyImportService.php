<?php

namespace App\Services;

use App\Models\FiscalYear;
use App\Models\Month;
use App\Models\MonthlyPortRecord;
use App\Models\Port;
use App\Models\RevenueCenter;
use App\Models\RevenueRecord;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\IOFactory;

class SinglePortMonthlyImportService
{
    /**
     * خريطة الكلمات المفتاحية لمطابقة الأعمدة والحقول التشغيلية
     */
    protected static array $fieldAliases = [
        'total_container_ships' => [
            'total_container_ships', 'بواخر الحاويات', 'سفن الحاويات', 'بواخر حاويات', 'حاويات كلي',
            'عدد بواخر الحاويات', 'بواخر الحاويات الكلي', 'container ships', 'container_ships',
        ],
        'general_cargo_ships' => [
            'general_cargo_ships', 'بواخر البضائع العامة', 'سفن البضائع العامة', 'بضائع عامة بواخر',
            'عدد البواخر المتنوعة', 'البواخر المتنوعة', 'سفن متنوعة', 'general cargo ships', 'cargo_ships',
        ],
        'oil_tankers_count' => [
            'oil_tankers_count', 'الناقلات النفطية', 'ناقلات النفط', 'بواخر النفط', 'ناقلات نفطية',
            'عدد الناقلات النفطية', 'عدد ناقلات النفط', 'oil tankers', 'tankers',
        ],
        'car_carrier_ships' => [
            'car_carrier_ships', 'ناقلات السيارات', 'سفن السيارات', 'حاملات السيارات', 'بواخر السيارات',
            'عدد ناقلات السيارات', 'car carrier', 'car_carriers',
        ],
        'imported_cars_count' => [
            'imported_cars_count', 'عدد السيارات المستوردة', 'السيارات المستوردة', 'عدد السيارات',
            'سيارات مستوردة عدد', 'imported cars', 'cars_count',
        ],
        'imported_cars_weight_tons' => [
            'imported_cars_weight_tons', 'وزن السيارات بالطن', 'الوزن بالطن - سيارات', 'وزن السيارات',
            'أوزان السيارات', 'cars weight', 'cars_weight_tons',
        ],
        'imported_containers_weight_tons' => [
            'imported_containers_weight_tons', 'الوزن بالطن - الحاويات المستوردة', 'وزن الحاويات المستوردة',
            'وزن الحاويات المستوردة بالطن', 'وزن مستورد حاويات', 'imported containers weight',
        ],
        'imported_containers_count' => [
            'imported_containers_count', 'عدد الحاويات المستوردة', 'الحاويات المستوردة كلي', 'مجموع الحاويات المستوردة',
        ],
        'imported_20ft' => [
            'imported_20ft', 'قدم 20 مستوردة', '20 قدم مستوردة', 'مستورد 20', 'حاويات 20 مستورد',
            '20ft مستوردة', '20 قدم مستورد', 'imp 20', 'imp_20',
        ],
        'imported_40ft' => [
            'imported_40ft', 'قدم 40 مستوردة', '40 قدم مستوردة', 'مستورد 40', 'حاويات 40 مستورد',
            '40ft مستوردة', '40 قدم مستورد', 'imp 40', 'imp_40',
        ],
        'imported_45ft' => [
            'imported_45ft', 'قدم 45 مستوردة', '45 قدم مستوردة', 'مستورد 45', 'حاويات 45 مستورد',
            '45ft مستوردة', '45 قدم مستورد', 'imp 45', 'imp_45',
        ],
        'exported_empty_count' => [
            'exported_empty_count', 'عدد الحاويات المصدرة فارغة', 'حاويات مصدرة فارغة', 'فارغة مصدرة',
            'مصدر فارغ', 'فارغ مصدر', 'فارغة', 'exported empty', 'empty containers',
        ],
        'exported_full_count' => [
            'exported_full_count', 'عدد الحاويات المصدرة مملوءة', 'حاويات مصدرة مملوءة', 'مملوءة مصدرة',
            'مصدر مملوءة', 'مليان مصدر', 'مملوءة', 'exported full', 'full containers',
        ],
        'exported_full_weight_tons' => [
            'exported_full_weight_tons', 'الوزن بالطن للمصدر المليان', 'وزن الحاويات المصدرة', 'وزن المصدر المليان',
            'وزن المصدر المملوء', 'exported full weight',
        ],
        'exported_20ft' => [
            'exported_20ft', 'قدم 20 مصدرة', '20 قدم مصدرة', 'مصدر 20', 'حاويات 20 مصدر',
            '20ft مصدرة', '20 قدم مصدر', 'exp 20', 'exp_20',
        ],
        'exported_40ft' => [
            'exported_40ft', 'قدم 40 مصدرة', '40 قدم مصدرة', 'مصدر 40', 'حاويات 40 مصدر',
            '40ft مصدرة', '40 قدم مصدر', 'exp 40', 'exp_40',
        ],
        'exported_45ft' => [
            'exported_45ft', 'قدم 45 مصدرة', '45 قدم مصدرة', 'مصدر 45', 'حاويات 45 مصدر',
            '45ft مصدرة', '45 قدم مصدر', 'exp 45', 'exp_45',
        ],
        'general_cargo_weight_tons' => [
            'general_cargo_weight_tons', 'الوزن بالطن - بضائع عامة', 'بضائع عامة بالطن', 'وزن البضائع العامة',
            'بضائع عامة', 'general cargo weight', 'cargo weight',
        ],
        'oil_exported_tons' => [
            'oil_exported_tons', 'نفط ومشتقاته مصدر بالطن', 'نفط مصدر بالطن', 'مشتقات نفطية مصدرة',
            'نفط مصدر', 'oil exported', 'exported oil',
        ],
        'oil_imported_tons' => [
            'oil_imported_tons', 'نفط ومشتقاته مستورد بالطن', 'نفط مستورد بالطن', 'مشتقات نفطية مستوردة',
            'نفط مستورد', 'oil imported', 'imported oil',
        ],
        'total_revenue' => [
            'total_revenue', 'الإيراد الكلي', 'الإيراد المالي', 'الإيراد الكلي - دينار', 'إجمالي الإيراد',
            'revenue', 'total revenue',
        ],
    ];

    /**
     * تنفيذ استيراد ملف إكسل لميناء محدد وشهر محدد وسنة محددة
     */
    public function import(int $portId, int $fiscalYearId, int $monthId, string $filePath): array
    {
        $port = Port::findOrFail($portId);
        $fiscalYear = FiscalYear::findOrFail($fiscalYearId);
        $month = Month::findOrFail($monthId);

        $spreadsheet = IOFactory::load($filePath);
        
        // البحث عن ورقة العمل الأنسب (إن وجدت ورقة باسم الميناء أو الورقة النشطة)
        $sheet = $spreadsheet->getActiveSheet();
        foreach ($spreadsheet->getAllSheets() as $candidateSheet) {
            $sheetTitle = trim($candidateSheet->getTitle());
            if (str_contains($sheetTitle, $port->name_ar) || str_contains($port->name_ar, $sheetTitle)) {
                $sheet = $candidateSheet;
                break;
            }
        }

        $rows = $sheet->toArray(null, true, true, true);
        if (empty($rows)) {
            throw new \Exception('ملف الإكسل فارغ ولا يحتوي على بيانات.');
        }

        // استخراج البيانات بحسب شكل الملف (جدول أفقي أو قائمة عمودية)
        $extractedData = $this->extractValuesFromRows($rows, $portId, $fiscalYearId, $monthId);

        // الحسابات التلقائية والتكميلية
        $imp20 = (int) ($extractedData['imported_20ft'] ?? 0);
        $imp40 = (int) ($extractedData['imported_40ft'] ?? 0);
        $imp45 = (int) ($extractedData['imported_45ft'] ?? 0);
        $calcImpCount = $imp20 + $imp40 + $imp45;
        $calcImpTeu = $imp20 + ($imp40 * 2) + ($imp45 * 2);

        $expEmpty = (int) ($extractedData['exported_empty_count'] ?? 0);
        $expFull  = (int) ($extractedData['exported_full_count'] ?? 0);
        $calcExpCount = $expEmpty + $expFull;

        $exp20 = (int) ($extractedData['exported_20ft'] ?? 0);
        $exp40 = (int) ($extractedData['exported_40ft'] ?? 0);
        $exp45 = (int) ($extractedData['exported_45ft'] ?? 0);
        $calcExpTeu = $exp20 + ($exp40 * 2) + ($exp45 * 2);

        $oilExp = (float) ($extractedData['oil_exported_tons'] ?? 0);
        $oilImp = (float) ($extractedData['oil_imported_tons'] ?? 0);
        $calcOilTotal = $oilExp + $oilImp;

        // استعلام الإيراد التلقائي في حال كان 0
        $revenue = (float) ($extractedData['total_revenue'] ?? 0);
        if ($revenue <= 0) {
            $revenueCenter = RevenueCenter::where('port_id', $portId)->first();
            if ($revenueCenter) {
                $revRecord = RevenueRecord::where([
                    'revenue_center_id' => $revenueCenter->id,
                    'fiscal_year_id'    => $fiscalYearId,
                    'month_id'          => $monthId,
                ])->first();
                if ($revRecord) {
                    $revenue = (float) $revRecord->gross_revenue;
                }
            }
        }

        $recordData = [
            'port_id'                         => $portId,
            'fiscal_year_id'                  => $fiscalYearId,
            'month_id'                        => $monthId,

            // 1. حركة البواخر والسيارات
            'total_container_ships'           => (int) ($extractedData['total_container_ships'] ?? 0),
            'general_cargo_ships'             => (int) ($extractedData['general_cargo_ships'] ?? 0),
            'oil_tankers_count'               => (int) ($extractedData['oil_tankers_count'] ?? 0),
            'car_carrier_ships'               => (int) ($extractedData['car_carrier_ships'] ?? 0),
            'imported_cars_count'             => (int) ($extractedData['imported_cars_count'] ?? 0),
            'imported_cars_weight_tons'       => (float) ($extractedData['imported_cars_weight_tons'] ?? 0),

            // 2. الحاويات المستوردة
            'imported_containers_weight_tons' => (float) ($extractedData['imported_containers_weight_tons'] ?? 0),
            'imported_containers_count'       => (!empty($extractedData['imported_containers_count']) && (int)$extractedData['imported_containers_count'] > 0)
                                                    ? (int)$extractedData['imported_containers_count']
                                                    : $calcImpCount,
            'imported_20ft'                   => $imp20,
            'imported_40ft'                   => $imp40,
            'imported_45ft'                   => $imp45,
            'imported_teu'                    => $calcImpTeu,

            // 3. الحاويات المصدرة
            'exported_empty_count'            => $expEmpty,
            'exported_full_count'             => $expFull,
            'exported_full_weight_tons'       => (float) ($extractedData['exported_full_weight_tons'] ?? 0),
            'exported_containers_count'       => $calcExpCount,
            'exported_20ft'                   => $exp20,
            'exported_40ft'                   => $exp40,
            'exported_45ft'                   => $exp45,
            'exported_teu'                    => $calcExpTeu,

            // 4. البضائع العامة
            'general_cargo_weight_tons'       => (float) ($extractedData['general_cargo_weight_tons'] ?? 0),

            // 5. النفط والمشتقات
            'oil_exported_tons'               => $oilExp,
            'oil_imported_tons'               => $oilImp,
            'oil_total_tons'                  => $calcOilTotal,

            // 6. الإيراد والبيانات الإدارية
            'total_revenue'                   => $revenue,
            'status'                          => 'approved',
            'created_by'                      => Auth::id() ?? 1,
        ];

        // الحفظ أو التحديث
        $existing = MonthlyPortRecord::withTrashed()->where([
            'port_id'        => $portId,
            'fiscal_year_id' => $fiscalYearId,
            'month_id'       => $monthId,
        ])->first();

        $actionType = 'created';
        if ($existing) {
            if ($existing->trashed()) {
                $existing->restore();
            }
            $existing->update($recordData);
            $record = $existing;
            $actionType = 'updated';
        } else {
            $record = MonthlyPortRecord::create($recordData);
        }

        ActivityLogger::log(
            $actionType === 'created' ? 'imported' : 'updated',
            "استيراد بيانات تشغيلية من Excel لميناء ({$port->name_ar}) - شهر ({$month->name_ar}) سنة ({$fiscalYear->year})",
            MonthlyPortRecord::class,
            $record->id,
            $recordData
        );

        return [
            'status'      => true,
            'action'      => $actionType,
            'record'      => $record,
            'port_name'   => $port->name_ar,
            'month_name'  => $month->name_ar,
            'year'        => $fiscalYear->year,
            'total_ships' => $record->total_ships,
            'total_teu'   => $record->total_teu,
            'total_cargo' => $record->total_tonnage,
        ];
    }

    /**
     * استخراج القيم من صفوف الإكسل بمرونة (يدعم الجداول الأفقية والعمودية)
     */
    protected function extractValuesFromRows(array $rows, int $portId, int $fiscalYearId, int $monthId): array
    {
        $result = [];

        // الفحص الأول: هل الملف جدول أفقي برؤوس أعمدة في الصف 1 أو 2؟
        $headerRowIndex = null;
        $matchedColumns = [];

        foreach ($rows as $rIdx => $row) {
            $rowMatches = [];
            foreach ($row as $colLetter => $cellValue) {
                if ($cellValue === null) continue;
                $normalized = $this->normalizeString((string) $cellValue);
                if (empty($normalized)) continue;

                foreach (static::$fieldAliases as $field => $aliases) {
                    if (isset($rowMatches[$field])) continue;
                    foreach ($aliases as $alias) {
                        if ($this->stringMatches($normalized, $alias)) {
                            $rowMatches[$field] = $colLetter;
                            break;
                        }
                    }
                }
            }

            // إذا وجدنا 3 أعمدة مطابقة أو أكثر، فهذا هو صف الترويسة الأفقي
            if (count($rowMatches) >= 3) {
                $headerRowIndex = $rIdx;
                $matchedColumns = $rowMatches;
                break;
            }
        }

        if ($headerRowIndex !== null && !empty($matchedColumns)) {
            // جدول أفقي: نبحث عن صف البيانات المطابق أو صف البيانات التالي
            $targetDataRow = null;

            // إذا كان في الملف صفوف متعددة، نحاول مطابقة صف يحتوي على نفس port_id و month_id إن وُجِدا
            foreach ($rows as $rIdx => $row) {
                if ($rIdx <= $headerRowIndex) continue;
                
                // التأكد أن الصف ليس فارغاً تماماً
                $hasData = false;
                foreach ($row as $val) {
                    if ($val !== null && trim((string)$val) !== '') {
                        $hasData = true;
                        break;
                    }
                }
                if (!$hasData) continue;

                // فحص إذا كان الصف يحدد port_id أو month_id
                $rowPort = null;
                $rowMonth = null;
                foreach ($row as $col => $val) {
                    $headerVal = (string) ($rows[$headerRowIndex][$col] ?? '');
                    if (str_contains($headerVal, 'port_id') || str_contains($headerVal, 'الميناء')) {
                        $rowPort = (int) $val;
                    }
                    if (str_contains($headerVal, 'month_id') || str_contains($headerVal, 'الشهر')) {
                        $rowMonth = (int) $val;
                    }
                }

                // إذا طابق الميناء والشهر، أو لم تكن هناك أعمدة شرطية مسبقة، نعتمد هذا الصف
                if (($rowPort === null || $rowPort === $portId) && ($rowMonth === null || $rowMonth === $monthId)) {
                    $targetDataRow = $row;
                    break;
                }
            }

            // إذا لم نجد صفاً مشروطاً، نأخذ أول صف بيانات بعد الترويسة
            if ($targetDataRow === null) {
                foreach ($rows as $rIdx => $row) {
                    if ($rIdx > $headerRowIndex) {
                        $targetDataRow = $row;
                        break;
                    }
                }
            }

            if ($targetDataRow) {
                foreach ($matchedColumns as $field => $colLetter) {
                    $val = $targetDataRow[$colLetter] ?? 0;
                    $result[$field] = $this->cleanNumber($val);
                }
                return $result;
            }
        }

        // الفحص الثاني: الجدول العمودي (Key-Value)
        // العمود الأول يحتوي على اسم الحقل/المؤشر والعمود الثاني يحتوي على قيمته
        foreach ($rows as $row) {
            $cells = array_values(array_filter($row, fn($c) => $c !== null && trim((string)$c) !== ''));
            if (count($cells) >= 2) {
                $label = $this->normalizeString((string)$cells[0]);
                $val = $cells[1];

                foreach (static::$fieldAliases as $field => $aliases) {
                    if (isset($result[$field])) continue;
                    foreach ($aliases as $alias) {
                        if ($this->stringMatches($label, $alias)) {
                            $result[$field] = $this->cleanNumber($val);
                            break 2;
                        }
                    }
                }
            }
        }

        return $result;
    }

    /**
     * تنظيف الأرقام وإزالة الفواصل والرموز
     */
    protected function cleanNumber(mixed $val): float
    {
        if (is_numeric($val)) {
            return (float) $val;
        }

        if (is_string($val)) {
            $clean = str_replace([',', ' ', 'د.ع', 'دينار', 'طن', 'حاوية', '$'], '', trim($val));
            return is_numeric($clean) ? (float) $clean : 0.0;
        }

        return 0.0;
    }

    /**
     * تنظيف النص للمطابقة
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
     * مطابقة النص مع الكلمة المفتاحية
     */
    protected function stringMatches(string $normalized, string $alias): bool
    {
        $aliasNorm = $this->normalizeString($alias);
        return str_contains($normalized, $aliasNorm) || str_contains($aliasNorm, $normalized);
    }
}
