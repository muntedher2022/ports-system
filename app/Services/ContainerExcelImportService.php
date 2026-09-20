<?php

namespace App\Services;

use App\Models\ContainerEntity;
use App\Models\ContainerItem;
use App\Models\ContainerStatusRecord;
use App\Models\FiscalYear;
use App\Models\Month;
use App\Models\Port;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use App\Services\ActivityLogger;

class ContainerExcelImportService
{
    /**
     * تطبيع وتنظيف النصوص العربية للمطابقة الذكية
     */
    public static function normalizeArabic(?string $str): string
    {
        if (empty($str)) return '';
        $str = trim((string)$str);
        $str = preg_replace('/[أإآ]/u', 'ا', $str);
        $str = preg_replace('/ة/u', 'ه', $str);
        $str = preg_replace('/ى/u', 'ي', $str);
        $str = preg_replace('/[^\p{L}\p{N}\s]/u', '', $str);
        $str = preg_replace('/\s+/u', ' ', $str);
        return mb_strtolower(trim($str));
    }

    public static array $charMap = [
        'A' => 10, 'B' => 12, 'C' => 13, 'D' => 14, 'E' => 15, 'F' => 16, 'G' => 17, 'H' => 18, 'I' => 19, 'J' => 20,
        'K' => 21, 'L' => 23, 'M' => 24, 'N' => 25, 'O' => 26, 'P' => 27, 'Q' => 28, 'R' => 29, 'S' => 30, 'T' => 31,
        'U' => 32, 'V' => 34, 'W' => 35, 'X' => 36, 'Y' => 37, 'Z' => 38,
    ];

    /**
     * دليل وكالات وخطوط الملاحة وشركات التأجير المعيارية العالمية
     */
    public static array $knownOwners = [
        // الخطوط الكبرى
        "MSK" => "Maersk Line", "MSF" => "Maersk Line", "MAE" => "Maersk Line", "MSW" => "Maersk Line", "MSM" => "Maersk Line", "MSD" => "Maersk Line", "MSN" => "Maersk Line", "MSB" => "Maersk Line", "MRK" => "Maersk Line",
        "MSC" => "Mediterranean Shipping Co", "MED" => "Mediterranean Shipping Co",
        "CMA" => "CMA CGM", "CMU" => "CMA CGM", "CGM" => "CMA CGM",
        "COS" => "COSCO Shipping", "CCL" => "COSCO Shipping", "COA" => "COSCO Shipping",
        "HLC" => "Hapag-Lloyd", "HAA" => "Hapag-Lloyd", "HLB" => "Hapag-Lloyd", "HLX" => "Hapag-Lloyd",
        "ONE" => "Ocean Network Express", "EGH" => "Evergreen Line", "EGL" => "Evergreen Line", "EMC" => "Evergreen Line",
        "YML" => "Yang Ming Marine Transport", "HMM" => "Hyundai Merchant Marine", "HDM" => "Hyundai Merchant Marine",
        "PIL" => "Pacific International Lines", "ZIM" => "ZIM Integrated Shipping", "WHL" => "Wan Hai Lines",
        "OOL" => "OOCL", "OOC" => "OOCL", "NYK" => "NYK Line", "SUD" => "Hamburg Süd", "UAC" => "UASC", "PON" => "P&O Nedlloyd",
        
        // شركات التأجير الكبرى
        "TRH" => "Triton Container Leasing", "TGH" => "Triton Container Leasing", "TLL" => "Triton Leasing", "TGB" => "Triton Leasing", "TII" => "Triton Leasing",
        "TCN" => "Textainer Leasing", "TCL" => "Textainer Leasing", "TEM" => "Textainer Leasing", "TEX" => "Textainer Leasing",
        "FSC" => "Florens Leasing", "FLK" => "Florens Leasing", "FCI" => "Florens Leasing",
        "GAT" => "CAI International", "CAI" => "CAI International", "CAX" => "CAI International", "CAP" => "CAI International",
        "SOU" => "Seaco Global", "GES" => "Seacube Containers", "BMO" => "Beacon Intermodal Leasing", "BMS" => "Beacon Intermodal",
        "SJK" => "Seaco Global", "SEG" => "Seaco Global", "SEK" => "Seaco Global", "SEL" => "Seaco Global",
        
        // رموز إضافية مستخرجة من ملف الاكسل
        "AAC" => "AACON Container", "AIC" => "AIA Logistics", "ALM" => "Al-Majdouie", "ALX" => "Alexandria Shipping", 
        "AMC" => "Amcar Line", "AMF" => "Amficon", "APH" => "American President Lines", "APM" => "APM Terminals", 
        "APR" => "APL Container", "APZ" => "APL Lines", "ASL" => "ASEAN Seas Line", "AXI" => "Axis Container", 
        "BAX" => "Bax Global", "BEA" => "Beacon Leasing", "BFS" => "BNSF Railway", "BHC" => "Bridgehead Container", 
        "BLJ" => "Baluja Shipping", "BSI" => "Blue Sky Intermodal", "BXB" => "Box Marine", "CAA" => "Container Applications Inc", 
        "CAR" => "Caru Containers", "CBH" => "China Base", "CCU" => "Container Corporation", "CIC" => "Crest Container", 
        "CIM" => "CIMC Containers", "CIN" => "Intermodal", "CIP" => "Capital Intermodal", "CKL" => "CK Line", 
        "CLH" => "Clarendon Container", "COR" => "Cronos Container", "CPS" => "Compass Containers", "CRL" => "Cronos Leasing", 
        "CRS" => "Cronos Group", "CRT" => "Crest Intermodal", "CRX" => "Cronos Containers", "CSD" => "China Shipping", 
        "CSK" => "CSKB Container", "CSL" => "China Shipping Line", "CSM" => "China Shipping Container", "CSN" => "China Shipping", 
        "CSY" => "Cosco Container", "CUL" => "China United Lines", "CXD" => "CAI Container", "CXS" => "CAI Shipping", 
        "DFS" => "Dan Zas Express", "DOL" => "Dolphin Containers", "DPW" => "DP World", "DRY" => "Dry Logistics", 
        "DVR" => "Dolphin Maritime", "EAS" => "EAS Datong", "ECM" => "Econship", "EGS" => "Evergreen Leasing", 
        "EIS" => "Eimskip", "EIT" => "Eastern Express", "EMK" => "Emkay Lines", "EOL" => "Euroocean", 
        "ESD" => "East Shine Lines", "ESH" => "Econocaribe", "ESP" => "Espartana", "FAL" => "Falcon Container", 
        "FAN" => "Fesco Container", "FBI" => "FBL Logistics", "FBL" => "FBL Line", "FCG" => "Florens Group", 
        "FDC" => "First Domestic", "FES" => "Far Eastern Shipping", "FFA" => "Florens Asset", "FML" => "Federal Marine", 
        "FOR" => "Fortis Containers", "FTA" => "Freight Transport", "FTB" => "First Transport", "FWR" => "Forward Shipping", 
        "FYC" => "Feiyi Container", "GAO" => "Gateway Containers", "GCX" => "Global Container Express", "GJS" => "Gold Star Line", 
        "GLD" => "Gold Star Line", "GRM" => "Gramcar", "GVC" => "Global Village Container", "HAM" => "Hamburg Süd", 
        "HAS" => "Hasco Line", "HDX" => "Hyundai Express", "HJC" => "Hanjin Shipping", "HMC" => "Hyundai Merchant Marine", 
        "HNS" => "Hanseatic", "HPC" => "Hanjin Pacific", "HXI" => "Heung-A Shipping", "IEA" => "Intermodal Exchange", 
        "IKM" => "Intermotive", "IMT" => "Intermodal Transport", "INA" => "Interasia Lines", "INB" => "Interasia", 
        "INK" => "Interkong", "INL" => "Intermodal Lines", "IRS" => "IRISL Group", "JFS" => "Japan Freight", 
        "JTM" => "JTM Shipping", "KMT" => "KMTC Line", "KOC" => "K-Line", "KWL" => "Kawai Shipping", 
        "LCR" => "LCR Leasing", "LGE" => "LG Electronics", "LTI" => "Lojistik", "LYG" => "Lianyungang", 
        "MAG" => "Magical Leasing", "MAX" => "Maxicon Container", "MCR" => "McGrath", "MCS" => "MSC Leasing", 
        "MHC" => "Marine Transport", "MIE" => "Mitsubishi", "MLJ" => "Malaysian Line", "MMA" => "Maritime Marine", 
        "MNB" => "Minos Marine", "MOA" => "MOL Lines", "MOE" => "MOL Enterprise", "MOT" => "MOL Transport", 
        "MPT" => "Marine Pacific", "MRS" => "Maras Linhas", "MVI" => "Marine Express", "MZW" => "Mazu Shipping", 
        "NEW" => "Newport Container", "NLL" => "Neptune Orient Lines", "NXT" => "Next Intermodal", "OTP" => "Orient Express", 
        "PAL" => "Pan Asia Line", "PCI" => "Pacific Container", "PID" => "Pacific International", "PML" => "Pan Maritime", 
        "PPD" => "Pacific Plane", "PRS" => "Persian Gulf Line", "QNL" => "Qatar Navigation", "REG" => "Regional Container", 
        "RFC" => "Red Ferries", "RJC" => "Raj Logistics", "RWA" => "Rail World", "RXC" => "Roxbox", 
        "SAN" => "Sanmarine", "SCZ" => "Suez Canal", "SGC" => "Sea Global", "SKI" => "Skintainer", 
        "SMH" => "Samudera Shipping", "SNH" => "Sinokor Merchant Marine", "SSP" => "Sea Sky", "SSS" => "Sea Sky Shipping", 
        "SUU" => "Sunmarine", "SVW" => "Seven Wings", "SZL" => "Sino Cargo", "TCK" => "Triton Container", 
        "TDR" => "Triton Direct", "TDT" => "Triton Transport", "TEG" => "Textainer Group", "TGC" => "Triton Global", 
        "TIG" => "Tiger Containers", "TKC" => "Taiko Containers", "TLH" => "Triton Heavy", "TOL" => "Trans Ocean", 
        "TRD" => "Trident Container", "TRI" => "Triton Rental", "TRL" => "Triton International", "TRU" => "Triton Unit", 
        "TTN" => "Triton Tank", "TXG" => "Textainer Express", "TYM" => "Toyo Express", "UAC" => "United Arab Shipping", 
        "UES" => "UES International", "UET" => "UES Transit", "UNS" => "Uniship", "UNX" => "United Express", 
        "VSB" => "Vanguard Shipping", "VST" => "Vosta Container", "WHS" => "Wan Hai Lines", "WOS" => "World Ocean", 
        "WSC" => "World Shipping", "XHC" => "XH Container", "XIN" => "Xingang Shipping", "YMM" => "Yang Ming"
    ];

    /**
     * حساب رقم التحقق (Check Digit) لـ 10 رموز (4 أحرف + 6 أرقام تسلسلية)
     */
    public static function calculateCheckDigit(string $str10): int
    {
        $sum = 0;
        for ($i = 0; $i < 10; $i++) {
            $ch = $str10[$i] ?? '0';
            $val = is_numeric($ch) ? (int)$ch : (static::$charMap[$ch] ?? 0);
            $sum += $val * (1 << $i);
        }
        $rem = $sum % 11;
        return ($rem === 10) ? 0 : $rem;
    }

    /**
     * تدقيق رقم الحاوية وفق المواصفة القياسية الدولية ISO 6346 واستنباط الأخطاء والنواقص ذكياً
     */
    public static function validateIsoContainerNumber(string $containerNo): array
    {
        $clean = strtoupper(trim(preg_replace('/[^A-Za-z0-9]/', '', $containerNo)));
        $len = strlen($clean);

        // ─── الحالة 1: نقص الحرف الرابع (3 أحرف + 7 أرقام = 10 رموز) مثل WHL5355998 أو TRI9820209 ───
        if ($len === 10 && preg_match('/^([A-Z]{3})([0-9]{6})([0-9])$/', $clean, $matches)) {
            $prefix3 = $matches[1];
            $serial6 = $matches[2];
            $targetDigit = (int)$matches[3];

            // فحص فئات المعدة القياسية (U: حاوية قياسية، J: معدة ملحقة، Z: مقطورة/شاسي) ثم باقي الحروف
            $candidateChars = ['U', 'J', 'Z'];
            $inferredChar = null;
            foreach ($candidateChars as $char) {
                if (static::calculateCheckDigit($prefix3 . $char . $serial6) === $targetDigit) {
                    $inferredChar = $char;
                    break;
                }
            }
            if (!$inferredChar) {
                foreach (range('A', 'Z') as $char) {
                    if (static::calculateCheckDigit($prefix3 . $char . $serial6) === $targetDigit) {
                        $inferredChar = $char;
                        break;
                    }
                }
            }

            if ($inferredChar) {
                $inferredNum = $prefix3 . $inferredChar . $serial6 . $targetDigit;
                $ownerName = static::$knownOwners[$prefix3] ?? null;
                $ownerLabel = $ownerName ? " ({$ownerName})" : "";
                return [
                    'is_valid'        => false,
                    'code'            => 'MISSING_4TH_CHAR',
                    'reason'          => "نقص الحرف الرابع: المقترح {$inferredNum}{$ownerLabel}",
                    'inferred_number' => $inferredNum,
                    'owner_name'      => $ownerName,
                    'expected'        => $targetDigit,
                    'actual'          => $containerNo,
                ];
            }
        }

        // ─── الحالة 2: نقص الحرف الرابع ورقم التحقق (3 أحرف + 6 أرقام = 9 رموز) مثل WHL535599 ───
        if ($len === 9 && preg_match('/^([A-Z]{3})([0-9]{6})$/', $clean, $matches)) {
            $prefix3 = $matches[1];
            $serial6 = $matches[2];
            $cd = static::calculateCheckDigit($prefix3 . 'U' . $serial6);
            $inferredNum = $prefix3 . 'U' . $serial6 . $cd;
            $ownerName = static::$knownOwners[$prefix3] ?? null;
            $ownerLabel = $ownerName ? " ({$ownerName})" : "";

            return [
                'is_valid'        => false,
                'code'            => 'MISSING_4TH_CHAR',
                'reason'          => "نقص الحرف الرابع ورقم التحقق: المقترح {$inferredNum}{$ownerLabel}",
                'inferred_number' => $inferredNum,
                'owner_name'      => $ownerName,
                'expected'        => $cd,
                'actual'          => $containerNo,
            ];
        }

        // ─── الحالة 3: الطول العام غير مطابق لـ 11 رمزاً ───
        if ($len !== 11) {
            return [
                'is_valid'        => false,
                'code'            => 'INVALID_LENGTH',
                'reason'          => "طول رقم الحاوية (يتطلب 11 رمزاً، والمدخل: {$len} رمز)",
                'inferred_number' => null,
                'owner_name'      => null,
                'expected'        => null,
                'actual'          => $containerNo,
            ];
        }

        // ─── الحالة 4: التنسيق العام غير مطابق لـ 4 أحرف + 7 أرقام ───
        if (!preg_match('/^[A-Z]{4}[0-9]{7}$/', $clean)) {
            return [
                'is_valid'        => false,
                'code'            => 'INVALID_FORMAT',
                'reason'          => "تنسيق رقم الحاوية (يتطلب 4 أحرف و7 أرقام)",
                'inferred_number' => null,
                'owner_name'      => null,
                'expected'        => null,
                'actual'          => $containerNo,
            ];
        }

        // ─── الحالة 5: تدقيق رقم التحقق (11 رمزاً) واستنباط الأخطاء الطباعية بالاعتماد على وكالات الشحن ───
        $prefix4 = substr($clean, 0, 4);
        $serial6 = substr($clean, 4, 6);
        $actualCheckDigit = (int) $clean[10];
        $calcCheckDigit = static::calculateCheckDigit($prefix4 . $serial6);

        if ($calcCheckDigit !== $actualCheckDigit) {
            // محاولة ذكية لاستنباط خطأ طباعي في أحد الحروف الأربعة بالاعتماد على دليل الوكالات الشاحنة ورقم التحقق
            $bestInferred = null;
            $bestOwner = null;

            for ($pos = 0; $pos < 4; $pos++) {
                $tryPositions = ($pos === 0) ? 2 : (($pos === 1) ? 3 : (($pos === 2) ? 1 : 0));
                $origChar = $prefix4[$tryPositions];

                foreach (range('A', 'Z') as $subChar) {
                    if ($subChar === $origChar) continue;
                    $testPrefix = $prefix4;
                    $testPrefix[$tryPositions] = $subChar;

                    if (static::calculateCheckDigit($testPrefix . $serial6) === $actualCheckDigit) {
                        $p3 = substr($testPrefix, 0, 3);
                        if (isset(static::$knownOwners[$p3])) {
                            $bestInferred = $testPrefix . $serial6 . $actualCheckDigit;
                            $bestOwner = static::$knownOwners[$p3];
                            break 2;
                        }
                    }
                }
            }

            if ($bestInferred) {
                $reasonText = "رقم التحقق الأخير (المتوقع: {$calcCheckDigit}، المدون: {$actualCheckDigit}) - المقترح: {$bestInferred} ({$bestOwner})";
                return [
                    'is_valid'        => false,
                    'code'            => 'CHECKSUM_MISMATCH',
                    'reason'          => $reasonText,
                    'inferred_number' => $bestInferred,
                    'owner_name'      => $bestOwner,
                    'expected'        => $calcCheckDigit,
                    'actual'          => $actualCheckDigit,
                ];
            }

            return [
                'is_valid'        => false,
                'code'            => 'CHECKSUM_MISMATCH',
                'reason'          => "رقم التحقق الأخير (المتوقع: {$calcCheckDigit}، المدون: {$actualCheckDigit})",
                'inferred_number' => $prefix4 . $serial6 . $calcCheckDigit,
                'owner_name'      => static::$knownOwners[substr($prefix4, 0, 3)] ?? null,
                'expected'        => $calcCheckDigit,
                'actual'          => $actualCheckDigit,
            ];
        }

        return [
            'is_valid'        => true,
            'code'            => 'VALID',
            'reason'          => null,
            'inferred_number' => $clean,
            'owner_name'      => static::$knownOwners[substr($prefix4, 0, 3)] ?? null,
            'expected'        => $calcCheckDigit,
            'actual'          => $actualCheckDigit,
        ];
    }

    /**
     * مطابقة اسم ورقة العمل مع جهات الحاويات
     */
    public static function resolveEntity(string $sheetName): ?ContainerEntity
    {
        $clean = static::normalizeArabic($sheetName);

        if (str_contains($clean, 'تجاري') || str_contains($clean, 'خاص') || str_contains($clean, 'شركات') || str_contains($clean, 'اهلي')) {
            return ContainerEntity::where('entity_type', 'private')->first()
                ?? ContainerEntity::where('name_ar', 'like', '%الخاص%')->first();
        }

        $entities = ContainerEntity::all();

        // 1. Exact match
        foreach ($entities as $e) {
            if (static::normalizeArabic($e->name_ar) === $clean) {
                return $e;
            }
        }

        // 2. Fuzzy words matching
        foreach ($entities as $e) {
            $eNorm = static::normalizeArabic($e->name_ar);
            $wordsE = array_filter(explode(' ', str_replace(['وزاره', 'محافظه', 'رئاسه', 'ال'], '', $eNorm)));
            $wordsSh = array_filter(explode(' ', str_replace(['وزاره', 'محافظه', 'رئاسه', 'ال'], '', $clean)));

            $intersect = array_intersect($wordsE, $wordsSh);
            if (count($intersect) >= min(count($wordsE), count($wordsSh)) && count($intersect) > 0) {
                return $e;
            }

            // Special cases like الاسكان والاعمار
            if ((str_contains($clean, 'اسكان') || str_contains($clean, 'اعمار')) && (str_contains($eNorm, 'اسكان') || str_contains($eNorm, 'اعمار'))) {
                return $e;
            }
            if ((str_contains($clean, 'انسيان') || str_contains($clean, 'انسان')) && (str_contains($eNorm, 'انسيان') || str_contains($eNorm, 'انسان'))) {
                return $e;
            }
        }

        return ContainerEntity::where('name_ar', 'like', "%{$sheetName}%")->first();
    }

    /**
     * مطابقة اسم الميناء من عمود الميناء
     */
    public static function resolvePort(?string $portStr, ?int $defaultPortId = null): ?Port
    {
        if (empty($portStr)) {
            return $defaultPortId ? Port::find($defaultPortId) : Port::first();
        }

        $clean = static::normalizeArabic($portStr);

        // 1. المطابقة المباشرة بالكلمات المفتاحية لموانئ العراق
        if (str_contains($clean, 'جنوب')) {
            return Port::where('name_ar', 'like', '%الجنوبي%')->first() ?? Port::find(2);
        }
        if (str_contains($clean, 'شمال')) {
            return Port::where('name_ar', 'like', '%الشمالي%')->first() ?? Port::find(1);
        }
        if (str_contains($clean, 'خور') || str_contains($clean, 'زبير')) {
            return Port::where('name_ar', 'like', '%خور الزبير%')->first() ?? Port::find(3);
        }
        if (str_contains($clean, 'فلوس')) {
            return Port::where('name_ar', 'like', '%فلو%')->first() ?? Port::find(4);
        }
        if (str_contains($clean, 'معقل')) {
            return Port::where('name_ar', 'like', '%معقل%')->first() ?? Port::find(5);
        }

        $ports = Port::all();

        // 2. المطابقة التامة للاسم بعد التطبيع
        foreach ($ports as $p) {
            if (static::normalizeArabic($p->name_ar) === $clean) {
                return $p;
            }
        }

        // 3. المطابقة الجزئية مع إزالة كلمة ميناء
        foreach ($ports as $p) {
            $pNorm = static::normalizeArabic($p->name_ar);
            $pWithoutMina = trim(preg_replace('/^ميناء\s+/u', '', $pNorm));
            $cleanWithoutMina = trim(preg_replace('/^ميناء\s+/u', '', $clean));
            if (!empty($pWithoutMina) && ($pWithoutMina === $cleanWithoutMina || str_contains($clean, $pWithoutMina) || str_contains($pNorm, $cleanWithoutMina))) {
                return $p;
            }
        }

        return $defaultPortId ? Port::find($defaultPortId) : Port::first();
    }

    /**
     * استيراد ومعالجة ملف الإكسل والمقاطعة الشهرية
     */
    public function import(
        string $filePath,
        int $fiscalYearId,
        int $monthId,
        string $containerType = 'abandoned',
        ?int $defaultPortId = null,
        ?string $reportDate = null,
        ?int $userId = null
    ): array {
        @ini_set('max_execution_time', '600');
        @set_time_limit(600);
        @ini_set('memory_limit', '1024M');

        if (!file_exists($filePath)) {
            throw new \Exception("الملف غير موجود في المسار المحدد: {$filePath}");
        }

        $userId = $userId ?? Auth::id() ?? 1;
        $reportDate = $reportDate ?: date('Y-m-d');

        $reader = IOFactory::createReaderForFile($filePath);
        if (method_exists($reader, 'setReadDataOnly')) {
            $reader->setReadDataOnly(true);
        }
        $spreadsheet = $reader->load($filePath);
        $sheetNames = $spreadsheet->getSheetNames();

        $allImportedContainers = [];
        $containersByPort = [];
        $skippedRows = 0;

        foreach ($sheetNames as $sheetName) {
            $sheet = $spreadsheet->getSheetByName($sheetName);
            if (!$sheet) continue;

            $entity = static::resolveEntity($sheetName);
            if (!$entity) {
                // If no matching entity found, fallback to private sector or first entity
                $entity = ContainerEntity::where('entity_type', 'private')->first() ?? ContainerEntity::first();
            }

            $highestRow = $sheet->getHighestRow();

            // Dynamic header detection (handles sheets where headers start in Row 2 or shifted columns like Babylon sheet)
            $headerRow = 1;
            $colMap = [
                'container_number' => 'B',
                'size'             => 'C',
                'ship_name'        => 'D',
                'goods_type'       => 'E',
                'consignee'        => 'F',
                'arrival_date'     => 'G',
                'berth'            => 'H',
                'port'             => 'I',
                'notes'            => 'J',
            ];

            $foundHeader = false;
            for ($r = 1; $r <= min(5, $highestRow); $r++) {
                for ($col = 'A'; $col <= 'K'; $col++) {
                    $val = static::normalizeArabic((string)$sheet->getCell($col . $r)->getValue());
                    if (str_contains($val, 'رقم الحاويه') || str_contains($val, 'رقم الحاويه') || str_contains($val, 'container no')) {
                        $headerRow = $r;
                        $foundHeader = true;
                        break 2;
                    }
                }
            }

            if ($foundHeader) {
                for ($col = 'A'; $col <= 'K'; $col++) {
                    $val = static::normalizeArabic((string)$sheet->getCell($col . $headerRow)->getValue());
                    if (empty($val)) continue;

                    if (str_contains($val, 'رقم الحاويه') || str_contains($val, 'رقم') || str_contains($val, 'container')) {
                        $colMap['container_number'] = $col;
                    } elseif (str_contains($val, 'حجم') || str_contains($val, 'size')) {
                        $colMap['size'] = $col;
                    } elseif (str_contains($val, 'باخره') || str_contains($val, 'سفينه') || str_contains($val, 'vessel') || str_contains($val, 'ship')) {
                        $colMap['ship_name'] = $col;
                    } elseif (str_contains($val, 'بضاعه') || str_contains($val, 'goods') || str_contains($val, 'cargo')) {
                        $colMap['goods_type'] = $col;
                    } elseif (str_contains($val, 'عائديه') || str_contains($val, 'مستلم') || str_contains($val, 'consignee')) {
                        $colMap['consignee'] = $col;
                    } elseif (str_contains($val, 'وصول') || str_contains($val, 'تاريخ') || str_contains($val, 'arrival') || str_contains($val, 'date')) {
                        $colMap['arrival_date'] = $col;
                    } elseif (str_contains($val, 'رصيف') || str_contains($val, 'ساحه') || str_contains($val, 'berth')) {
                        $colMap['berth'] = $col;
                    } elseif (str_contains($val, 'ميناء') || str_contains($val, 'port')) {
                        $colMap['port'] = $col;
                    } elseif (str_contains($val, 'ملاحظ') || str_contains($val, 'note') || str_contains($val, 'remark')) {
                        $colMap['notes'] = $col;
                    }
                }
            }

            for ($row = $headerRow + 1; $row <= $highestRow; $row++) {
                $cCol = $colMap['container_number'];
                $containerNo = strtoupper(trim((string)$sheet->getCell($cCol . $row)->getValue()));
                if (empty($containerNo) || strlen($containerNo) < 4 || in_array($containerNo, ['رقم الحاوية', 'رقم الحاويه', 'CONTAINER NO', 'NO'])) {
                    $skippedRows++;
                    continue;
                }

                $size = trim((string)$sheet->getCell(($colMap['size'] ?? 'C') . $row)->getValue());
                $shipName = trim((string)$sheet->getCell(($colMap['ship_name'] ?? 'D') . $row)->getValue());
                $goodsType = trim((string)$sheet->getCell(($colMap['goods_type'] ?? 'E') . $row)->getValue());
                $consignee = trim((string)$sheet->getCell(($colMap['consignee'] ?? 'F') . $row)->getValue());
                $rawDate = $sheet->getCell(($colMap['arrival_date'] ?? 'G') . $row)->getValue();
                $berth = trim((string)$sheet->getCell(($colMap['berth'] ?? 'H') . $row)->getValue());
                $portStr = isset($colMap['port']) ? trim((string)$sheet->getCell($colMap['port'] . $row)->getValue()) : '';
                $notes = isset($colMap['notes']) ? trim((string)$sheet->getCell($colMap['notes'] . $row)->getValue()) : '';

                // Resolve port
                $port = static::resolvePort($portStr, $defaultPortId);
                if (!$port) continue;

                // 1. تدقيق رقم الحاوية وفق المواصفة القياسية الدولية ISO 6346
                $isoCheck = static::validateIsoContainerNumber($containerNo);
                if (!$isoCheck['is_valid']) {
                    $warnIso = "[تنبيه رقابي: {$isoCheck['reason']}]";
                    $notes = !empty($notes) ? ($notes . ' | ' . $warnIso) : $warnIso;
                }

                // 2. تدقيق تاريخ وسنة الوصول
                $arrivalDate = null;
                $arrivalYear = '2015';
                $isMissingDate = false;

                if (is_numeric($rawDate)) {
                    try {
                        $dt = ExcelDate::excelToDateTimeObject($rawDate);
                        $arrivalDate = $dt->format('Y-m-d');
                        $arrivalYear = $dt->format('Y');
                    } catch (\Throwable $e) {
                        $arrivalYear = '2015';
                    }
                } elseif (is_string($rawDate) && !empty(trim($rawDate))) {
                    $rawDateTrim = trim($rawDate);
                    if (str_contains($rawDateTrim, 'متعدد') || str_contains($rawDateTrim, 'تواريخ')) {
                        $arrivalYear = 'تواريخ متعددة';
                    } elseif (str_contains($rawDateTrim, 'غير محدد') || str_contains($rawDateTrim, 'بدون')) {
                        $arrivalYear = 'غير محدد التاريخ';
                    } else {
                        if (preg_match('/(\d{4})/', $rawDateTrim, $m)) {
                            $arrivalYear = $m[1];
                        }
                        $cleanDateStr = str_replace(['/', '.'], '-', $rawDateTrim);
                        if ($time = strtotime($cleanDateStr)) {
                            $arrivalDate = date('Y-m-d', $time);
                            if (empty($arrivalYear) || $arrivalYear === (string)date('Y')) {
                                $arrivalYear = date('Y', $time);
                            }
                        }
                    }
                } else {
                    $isMissingDate = true;
                    $arrivalYear = '2015';
                    $warnMissingDate = "[تنبيه رقابي: تاريخ وسنة الوصول غير مدون في الشيت ومدرج افتراضياً ضمن 2015 فما دون]";
                    $notes = !empty($notes) ? ($notes . ' | ' . $warnMissingDate) : $warnMissingDate;
                }

                $itemData = [
                    'port_id'             => $port->id,
                    'container_entity_id' => $entity->id,
                    'fiscal_year_id'      => $fiscalYearId,
                    'month_id'            => $monthId,
                    'container_type'      => $containerType,
                    'container_number'    => $containerNo,
                    'size'                => $size ?: null,
                    'ship_name'           => $shipName ?: null,
                    'goods_type'          => $goodsType ?: null,
                    'consignee'           => $consignee ?: null,
                    'arrival_date'        => $arrivalDate,
                    'arrival_year'        => $arrivalYear,
                    'berth'               => $berth ?: null,
                    'notes'               => !empty($notes) ? $notes : null,
                    'status'              => 'in_port',
                    'created_by'          => $userId,
                    'updated_by'          => $userId,
                    '_raw_data'           => [
                        'entity_name'     => $entity->name_ar,
                        'port_name'       => $port->name_ar,
                        'iso_check'       => $isoCheck,
                        'is_missing_date' => $isMissingDate,
                        'original_notes'  => isset($colMap['notes']) ? trim((string)$sheet->getCell($colMap['notes'] . $row)->getValue()) : '',
                    ],
                ];

                $allImportedContainers[$port->id][$containerNo] = $itemData;
                $containersByPort[$port->id][] = $itemData;
            }
        }

        // Determine Previous Month for Reconciliation
        $currMonth = Month::find($monthId);
        $fiscalYearModel = FiscalYear::find($fiscalYearId);
        $currentYearNum = (int) ($fiscalYearModel?->year ?? date('Y'));

        $prevMonthId = null;
        $prevYearId = $fiscalYearId;

        if ($currMonth) {
            $prevMonthNumber = $currMonth->month_number - 1;
            if ($prevMonthNumber < 1) {
                $prevMonthNumber = 12;
                $prevYearObj = FiscalYear::where('year', $currentYearNum - 1)->first();
                $prevYearId = $prevYearObj?->id ?? $fiscalYearId;
            }
            $prevMonthObj = Month::where('month_number', $prevMonthNumber)->first();
            $prevMonthId = $prevMonthObj?->id;
        }

        $stats = [
            'total_imported'          => 0,
            'new_containers'          => 0,
            'discharged_count'        => 0,
            'missing_date_anomalies'  => [],
            'invalid_iso_anomalies'   => [],
            'prior_year_anomalies'    => [],
            'date_mismatch_anomalies' => [],
            'port_change_anomalies'   => [],
            'all_anomalies'           => [],
            'all_processed_rows'      => [],
            'audit_report_url'        => null,
            'audit_report_filename'   => null,
            'ports_processed'         => [],
        ];

        DB::transaction(function () use (
            $containersByPort,
            $allImportedContainers,
            $fiscalYearId,
            $monthId,
            $containerType,
            $reportDate,
            $userId,
            $prevMonthId,
            $prevYearId,
            $currentYearNum,
            &$stats
        ) {
            foreach ($containersByPort as $portId => $items) {
                $port = Port::find($portId);
                $portName = $port?->name_ar ?? "ميناء #{$portId}";

                // 1. Get or Create ContainerStatusRecord for this port & month (with withTrashed support)
                $record = ContainerStatusRecord::withTrashed()
                    ->where('port_id', $portId)
                    ->where('fiscal_year_id', $fiscalYearId)
                    ->where('month_id', $monthId)
                    ->where('container_type', $containerType)
                    ->first();

                if ($record) {
                    if ($record->trashed()) {
                        $record->restore();
                    }
                    $record->update([
                        'report_date' => $reportDate,
                        'updated_by'  => $userId,
                    ]);
                } else {
                    $record = ContainerStatusRecord::create([
                        'port_id'        => $portId,
                        'fiscal_year_id' => $fiscalYearId,
                        'month_id'       => $monthId,
                        'container_type' => $containerType,
                        'report_date'    => $reportDate,
                        'created_by'     => $userId,
                        'total_count'    => 0,
                    ]);
                }

                // 2. Previous Month Reconciliation for this Port & Container Type
                $dischargedForThisPort = 0;
                $prevNumbersList = [];
                $prevItems = collect();
                if ($prevMonthId) {
                    $prevRecord = ContainerStatusRecord::where('port_id', $portId)
                        ->where('fiscal_year_id', $prevYearId)
                        ->where('month_id', $prevMonthId)
                        ->where('container_type', $containerType)
                        ->first();

                    if ($prevRecord) {
                        $prevItems = ContainerItem::where('container_status_record_id', $prevRecord->id)
                            ->where('status', 'in_port')
                            ->get();

                        $prevNumbersList = $prevItems->pluck('container_number')->all();
                        $currentNumbersInFile = array_keys($allImportedContainers[$portId] ?? []);

                        foreach ($prevItems as $prevItem) {
                            if (!in_array($prevItem->container_number, $currentNumbersInFile)) {
                                // Container was present in previous month but is NO LONGER in current file -> Discharged!
                                $prevItem->update([
                                    'status'                   => 'discharged',
                                    'discharge_fiscal_year_id' => $fiscalYearId,
                                    'discharge_month_id'       => $monthId,
                                    'discharge_date'           => $reportDate,
                                    'updated_by'               => $userId,
                                ]);
                                $dischargedForThisPort++;
                                $stats['discharged_count']++;
                            }
                        }
                    }
                }

                // 3. Check for Anomalies (ISO + تاريخ مفقود + اختلاف تاريخ الوصول + اختلاف الميناء + مخالفة إضافة سنوات سابقة)
                $prevItemsByNumber = $prevItems->keyBy('container_number');

                foreach ($items as &$itm) {
                    $cNo = $itm['container_number'];
                    $arrYear = trim((string) $itm['arrival_year']);
                    $currDate = $itm['arrival_date'];
                    $rawInfo = $itm['_raw_data'] ?? [];
                    $entityName = $rawInfo['entity_name'] ?? (ContainerEntity::find($itm['container_entity_id'])?->name_ar ?? '');
                    $isoCheck = $rawInfo['iso_check'] ?? static::validateIsoContainerNumber($cNo);
                    $isMissingDate = $rawInfo['is_missing_date'] ?? false;
                    $origNotes = $rawInfo['original_notes'] ?? '';

                    $containerRemarks = [];
                    $rowSeverity = 'normal';

                    // A. Collect ISO Anomaly if invalid
                    if (!$isoCheck['is_valid']) {
                        $stats['invalid_iso_anomalies'][] = [
                            'port_name'        => $portName,
                            'container_number' => $cNo,
                            'entity_name'      => $entityName,
                            'reason'           => $isoCheck['reason'],
                        ];
                        $containerRemarks[] = $isoCheck['reason'];
                        $rowSeverity = 'danger';

                        $stats['all_anomalies'][] = [
                            'container_number' => $cNo,
                            'iso_status'       => 'غير مطابق (' . $isoCheck['code'] . ')',
                            'port_name'        => $portName,
                            'prev_port_name'   => '-',
                            'entity_name'      => $entityName,
                            'category'         => 'مخالفة رقم الحاوية القياسي (ISO 6346)',
                            'severity'         => 'danger',
                            'details'          => $isoCheck['reason'],
                            'action_taken'     => 'تم تسجيل الحاوية مع تثبيت الملاحظة الرقابية',
                            'arrival_year'     => $arrYear,
                            'arrival_date'     => $currDate ?: 'غير مدون',
                            'size'             => $itm['size'] ?? '-',
                            'ship_name'        => $itm['ship_name'] ?? '-',
                            'goods_type'       => $itm['goods_type'] ?? '-',
                            'consignee'        => $itm['consignee'] ?? '-',
                            'berth'            => $itm['berth'] ?? '-',
                            'original_notes'   => $origNotes,
                            'notes'            => $itm['notes'] ?? '',
                        ];
                    }

                    // B. Collect Missing Date Anomaly
                    if ($isMissingDate) {
                        $stats['missing_date_anomalies'][] = [
                            'port_name'        => $portName,
                            'container_number' => $cNo,
                            'entity_name'      => $entityName,
                        ];
                        $containerRemarks[] = "تاريخ الوصول غير مدون (أدرجت 2015 فما دون)";
                        if ($rowSeverity !== 'danger') $rowSeverity = 'warning';

                        $stats['all_anomalies'][] = [
                            'container_number' => $cNo,
                            'iso_status'       => $isoCheck['is_valid'] ? 'مطابق' : 'غير مطابق',
                            'port_name'        => $portName,
                            'prev_port_name'   => '-',
                            'entity_name'      => $entityName,
                            'category'         => 'تاريخ وصول غير مدون (مفقود)',
                            'severity'         => 'warning',
                            'details'          => 'حاوية لا تحتوي على تاريخ أو سنة وصول في شيت الإكسل المرفوع',
                            'action_taken'     => 'تم إدراجها افتراضياً ضمن فئة 2015 فما دون',
                            'arrival_year'     => '2015 (افتراضي)',
                            'arrival_date'     => 'غير مدون',
                            'size'             => $itm['size'] ?? '-',
                            'ship_name'        => $itm['ship_name'] ?? '-',
                            'goods_type'       => $itm['goods_type'] ?? '-',
                            'consignee'        => $itm['consignee'] ?? '-',
                            'berth'            => $itm['berth'] ?? '-',
                            'original_notes'   => $origNotes,
                            'notes'            => $itm['notes'] ?? '',
                        ];
                    }

                    // Search for any previous record of this container across any port
                    $prevContainerAnyPort = ContainerItem::where('container_number', $cNo)
                        ->where(function ($q) use ($fiscalYearId, $monthId) {
                            $q->where('fiscal_year_id', '<', $fiscalYearId)
                              ->orWhere(function ($q2) use ($fiscalYearId, $monthId) {
                                  $q2->where('fiscal_year_id', $fiscalYearId)
                                     ->where('month_id', '<', $monthId);
                              });
                        })
                        ->latest('id')
                        ->first();

                    $prevPortNameForThis = '-';
                    // C. Check for Port Change Anomaly
                    if ($prevContainerAnyPort && (int)$prevContainerAnyPort->port_id !== (int)$portId) {
                        $prevPortObj = Port::find($prevContainerAnyPort->port_id);
                        $prevPortName = $prevPortObj?->name_ar ?? 'ميناء سابق';
                        $prevPortNameForThis = $prevPortName;

                        $stats['port_change_anomalies'][] = [
                            'port_name'        => $portName,
                            'prev_port_name'   => $prevPortName,
                            'curr_port_name'   => $portName,
                            'container_number' => $cNo,
                            'entity_name'      => $entityName,
                        ];
                        $warnPort = "[تنبيه رقابي: تغيير الميناء عن السجل السابق (السابق: {$prevPortName} ⟵ الحالي: {$portName})]";
                        $itm['notes'] = !empty($itm['notes']) ? ($itm['notes'] . ' | ' . $warnPort) : $warnPort;

                        $containerRemarks[] = "تغيير الميناء (السابق: {$prevPortName} ⟵ الحالي: {$portName})";
                        if (!in_array($rowSeverity, ['danger', 'warning'])) $rowSeverity = 'info';

                        $stats['all_anomalies'][] = [
                            'container_number' => $cNo,
                            'iso_status'       => $isoCheck['is_valid'] ? 'مطابق' : 'غير مطابق',
                            'port_name'        => $portName,
                            'prev_port_name'   => $prevPortName,
                            'entity_name'      => $entityName,
                            'category'         => 'نقل / تغيير الميناء',
                            'severity'         => 'info',
                            'details'          => "تم تغيير الميناء المسجل للحاوية من ({$prevPortName}) إلى ({$portName})",
                            'action_taken'     => 'تم تحديث الميناء وربط الحاوية بالميناء الجديد',
                            'arrival_year'     => $arrYear,
                            'arrival_date'     => $currDate ?: 'غير مدون',
                            'size'             => $itm['size'] ?? '-',
                            'ship_name'        => $itm['ship_name'] ?? '-',
                            'goods_type'       => $itm['goods_type'] ?? '-',
                            'consignee'        => $itm['consignee'] ?? '-',
                            'berth'            => $itm['berth'] ?? '-',
                            'original_notes'   => $origNotes,
                            'notes'            => $itm['notes'] ?? '',
                        ];
                    }

                    // D. Check for Date Mismatch against previous records
                    $prevContainer = $prevItemsByNumber->get($cNo) ?? $prevContainerAnyPort;

                    if ($prevContainer) {
                        $prevDate = $prevContainer->arrival_date ? $prevContainer->arrival_date->format('Y-m-d') : null;
                        $prevYear = trim((string) $prevContainer->arrival_year);

                        $isDateMismatch = false;
                        $prevDateLabel = '';
                        $currDateLabel = '';

                        if (!empty($currDate) && !empty($prevDate) && $currDate !== $prevDate) {
                            $isDateMismatch = true;
                            $prevDateLabel = date('d/m/Y', strtotime($prevDate));
                            $currDateLabel = date('d/m/Y', strtotime($currDate));
                        } elseif (!empty($arrYear) && !empty($prevYear) && $arrYear !== $prevYear && $prevYear !== '2015' && $arrYear !== '2015') {
                            $isDateMismatch = true;
                            $prevDateLabel = $prevYear;
                            $currDateLabel = $arrYear;
                        }

                        if ($isDateMismatch) {
                            $stats['date_mismatch_anomalies'][] = [
                                'port_name'        => $portName,
                                'container_number' => $cNo,
                                'prev_date'        => (string)$prevDateLabel,
                                'curr_date'        => (string)$currDateLabel,
                                'entity_name'      => $entityName,
                            ];
                            $warnText = "[تنبيه رقابي: اختلاف تاريخ الوصول عن السجل السابق (السابق: {$prevDateLabel}، الحالي: {$currDateLabel})]";
                            $itm['notes'] = !empty($itm['notes']) ? ($itm['notes'] . ' | ' . $warnText) : $warnText;

                            $containerRemarks[] = "اختلاف تاريخ الوصول (السابق: {$prevDateLabel} ⟵ الحالي: {$currDateLabel})";
                            if ($rowSeverity !== 'danger') $rowSeverity = 'warning';

                            $stats['all_anomalies'][] = [
                                'container_number' => $cNo,
                                'iso_status'       => $isoCheck['is_valid'] ? 'مطابق' : 'غير مطابق',
                                'port_name'        => $portName,
                                'prev_port_name'   => '-',
                                'entity_name'      => $entityName,
                                'category'         => 'اختلاف تاريخ الوصول عن السجل السابق',
                                'severity'         => 'warning',
                                'details'          => "تاريخ/سنة الوصول الحالية ({$currDateLabel}) تختلف عما كان مسجلاً سابقاً ({$prevDateLabel})",
                                'action_taken'     => 'تم اعتماد التاريخ الجديد مع وسم الملاحظة الرقابية',
                                'arrival_year'     => $arrYear,
                                'arrival_date'     => $currDate ?: 'غير مدون',
                                'size'             => $itm['size'] ?? '-',
                                'ship_name'        => $itm['ship_name'] ?? '-',
                                'goods_type'       => $itm['goods_type'] ?? '-',
                                'consignee'        => $itm['consignee'] ?? '-',
                                'berth'            => $itm['berth'] ?? '-',
                                'original_notes'   => $origNotes,
                                'notes'            => $itm['notes'] ?? '',
                            ];
                        }
                    }

                    // E. Check for Prior-Year Additions Anomaly
                    if (!empty($prevNumbersList)) {
                        $isPriorYear = is_numeric($arrYear) && (int) $arrYear < $currentYearNum;
                        if ($isPriorYear && !in_array($cNo, $prevNumbersList) && (!$prevContainerAnyPort || (int)$prevContainerAnyPort->port_id === (int)$portId)) {
                            $stats['prior_year_anomalies'][] = [
                                'port_name'        => $portName,
                                'container_number' => $cNo,
                                'arrival_year'     => $arrYear,
                                'entity_name'      => $entityName,
                            ];
                            $warnPrior = "[تنبيه رقابي: حاوية بسنة سابقة ({$arrYear}) مضافة حديثاً لم تكن مسجلة في الشهر السابق]";
                            $itm['notes'] = !empty($itm['notes']) ? ($itm['notes'] . ' | ' . $warnPrior) : $warnPrior;

                            $containerRemarks[] = "حاوية بسنة سابقة ({$arrYear}) مضافة حديثاً";
                            if ($rowSeverity !== 'danger') $rowSeverity = 'warning';

                            $stats['all_anomalies'][] = [
                                'container_number' => $cNo,
                                'iso_status'       => $isoCheck['is_valid'] ? 'مطابق' : 'غير مطابق',
                                'port_name'        => $portName,
                                'prev_port_name'   => '-',
                                'entity_name'      => $entityName,
                                'category'         => 'إضافة حاوية بسنة سابقة لم تكن مسجلة',
                                'severity'         => 'warning',
                                'details'          => "حاوية مضافة حديثاً بسنة سابقة ({$arrYear}) ولم تكن مسجلة في الشهر السابق لهذا الميناء",
                                'action_taken'     => 'تم إدراجها ضمن سنة وصولها مع توثيق المخالفة الرقابية',
                                'arrival_year'     => $arrYear,
                                'arrival_date'     => $currDate ?: 'غير مدون',
                                'size'             => $itm['size'] ?? '-',
                                'ship_name'        => $itm['ship_name'] ?? '-',
                                'goods_type'       => $itm['goods_type'] ?? '-',
                                'consignee'        => $itm['consignee'] ?? '-',
                                'berth'            => $itm['berth'] ?? '-',
                                'original_notes'   => $origNotes,
                                'notes'            => $itm['notes'] ?? '',
                            ];
                        }
                    }

                    $hasAuditNote = !empty($containerRemarks);
                    $auditDetailsText = $hasAuditNote ? implode(' | ', $containerRemarks) : 'مطابق (لا توجد ملاحظات)';

                    $stats['all_processed_rows'][] = [
                        'container_number' => $cNo,
                        'suggested_number' => (!empty($isoCheck['inferred_number']) && $isoCheck['inferred_number'] !== $cNo) ? $isoCheck['inferred_number'] : '',
                        'iso_status'       => $isoCheck['is_valid'] ? 'مطابق' : ('غير مطابق (' . $isoCheck['code'] . ')'),
                        'port_name'        => $portName,
                        'prev_port_name'   => $prevPortNameForThis,
                        'entity_name'      => $entityName,
                        'size'             => $itm['size'] ?? '-',
                        'ship_name'        => $itm['ship_name'] ?? '-',
                        'goods_type'       => $itm['goods_type'] ?? '-',
                        'consignee'        => $itm['consignee'] ?? '-',
                        'arrival_date'     => $currDate ?: 'غير مدون',
                        'arrival_year'     => $arrYear,
                        'berth'            => $itm['berth'] ?? '-',
                        'original_notes'   => $origNotes,
                        'audit_details'    => $auditDetailsText,
                        'has_anomaly'      => $hasAuditNote,
                        'severity'         => $rowSeverity,
                    ];

                    // Remove helper _raw_data before DB insert
                    unset($itm['_raw_data']);
                }
                unset($itm);

                // 4. Clear existing items for current record/port/month to allow fresh sync from this upload
                ContainerItem::withTrashed()
                    ->where(function ($q) use ($record, $portId, $fiscalYearId, $monthId, $containerType) {
                        $q->where('container_status_record_id', $record->id)
                          ->orWhere(function ($q2) use ($portId, $fiscalYearId, $monthId, $containerType) {
                              $q2->where('port_id', $portId)
                                 ->where('fiscal_year_id', $fiscalYearId)
                                 ->where('month_id', $monthId)
                                 ->where('container_type', $containerType);
                          });
                    })
                    ->forceDelete();

                // 5. Insert all items for this month
                $portImportedCount = 0;
                foreach ($items as $item) {
                    $item['container_status_record_id'] = $record->id;
                    ContainerItem::create($item);
                    $portImportedCount++;
                    $stats['total_imported']++;
                }

                // 6. Automatically recalculate and sync ContainerStatusDetail for this record
                ContainerItem::syncRecordDetails($record->id);

                $stats['ports_processed'][$portId] = [
                    'port_name'        => $portName,
                    'record_id'        => $record->id,
                    'imported_count'   => $portImportedCount,
                    'discharged_count' => $dischargedForThisPort,
                    'final_total'      => $record->fresh()->total_count,
                ];
            }
        });

        // Always generate Excel Audit Report for full traceability and auditing
        if (!empty($stats['all_processed_rows'])) {
            $auditReport = static::generateAuditExcelReport(
                allProcessedRows: $stats['all_processed_rows'],
                allAnomalies: $stats['all_anomalies'],
                stats: $stats,
                fiscalYear: $fiscalYearModel,
                month: $currMonth,
                containerType: $containerType
            );

            if ($auditReport) {
                $stats['audit_report_url'] = $auditReport['download_url'];
                $stats['audit_report_filename'] = $auditReport['file_name'];
            }
        }

        // Log the import activity with lightweight stats
        $auditStats = [
            'total_imported'             => $stats['total_imported'],
            'discharged_count'           => $stats['discharged_count'],
            'missing_date_count'         => count($stats['missing_date_anomalies']),
            'invalid_iso_count'          => count($stats['invalid_iso_anomalies']),
            'prior_year_anomalies_count' => count($stats['prior_year_anomalies']),
            'date_mismatch_count'        => count($stats['date_mismatch_anomalies']),
            'port_change_count'          => count($stats['port_change_anomalies']),
            'total_anomalies_count'      => count($stats['all_anomalies']),
            'audit_report_filename'      => $stats['audit_report_filename'],
            'ports_processed'            => $stats['ports_processed'],
        ];

        ActivityLogger::log(
            'created',
            "استيراد ومعالجة ذكية لموقف الحاويات (" . ($containerType === 'dangerous' ? 'الخطرة' : 'المتخلفة') . ") لشهر {$currMonth?->name_ar} {$fiscalYearModel?->year} - إجمالي {$stats['total_imported']} حاوية، تخريج {$stats['discharged_count']} حاوية، ورصد " . count($stats['all_anomalies']) . " ملاحظة رقابية",
            ContainerStatusRecord::class,
            $stats['ports_processed'] ? reset($stats['ports_processed'])['record_id'] : null,
            $auditStats
        );

        return $stats;
    }

    /**
     * توليد ملف إكسل رسمي وشامل لكافة البيانات المرفوعة مع حقل تفاصيل الملاحظة الرقابية
     */
    public static function generateAuditExcelReport(
        array $allProcessedRows,
        array $allAnomalies,
        array $stats,
        ?FiscalYear $fiscalYear,
        ?Month $month,
        string $containerType = 'abandoned'
    ): ?array {
        try {
            $spreadsheet = new Spreadsheet();

            $typeLabel = $containerType === 'dangerous' ? 'الخطرة' : 'المتخلفة';
            $monthName = $month?->name_ar ?? 'الشهر الحالي';
            $yearName = $fiscalYear?->year ?? date('Y');
            $totalCount = count($allProcessedRows);
            $totalAnom = count($allAnomalies);
            $cleanCount = $totalCount - count(array_filter($allProcessedRows, fn($r) => !empty($r['has_anomaly'])));

            $headers = [
                'A' => 'ت',
                'B' => 'رقم الحاوية الحالي',
                'C' => 'رقم الحاوية الصحيح',
                'D' => 'تفاصيل الملاحظة الرقابية',
                'E' => 'الميناء',
                'F' => 'الجهة / الوزارة',
                'G' => 'الحجم',
                'H' => 'اسم الباخرة',
                'I' => 'نوع البضاعة',
                'J' => 'العائدية / المستلم',
                'K' => 'تاريخ الوصول',
                'L' => 'سنة الوصول',
                'M' => 'الرصيف',
                'N' => 'ملاحظات الشيت',
            ];

            $colWidths = [
                'A' => 8,
                'B' => 18,
                'C' => 18,
                'D' => 42,
                'E' => 20,
                'F' => 28,
                'G' => 10,
                'H' => 18,
                'I' => 20,
                'J' => 24,
                'K' => 14,
                'L' => 12,
                'M' => 12,
                'N' => 28,
            ];

            // ══════════════════════════════════════════════════════════════════
            // ورقة العمل 1: كافة البيانات المرفوعة مع حقل الملاحظات الرقابية
            // ══════════════════════════════════════════════════════════════════
            $sheet1 = $spreadsheet->getActiveSheet();
            $sheet1->setTitle('كافة البيانات والملاحظات');
            $sheet1->setRightToLeft(true);
            $sheet1->setShowGridLines(true);

            // Banner Title
            $sheet1->mergeCells('A2:N2');
            $sheet1->setCellValue('A2', 'الشركة العامة لموانئ العراق - المتابعة المركزية والعمليات');
            $sheet1->getStyle('A2')->getFont()->setBold(true)->setSize(14)->getColor()->setARGB('FFFFFFFF');
            $sheet1->getStyle('A2')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF0F172A');
            $sheet1->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $sheet1->getRowDimension(2)->setRowHeight(30);

            $sheet1->mergeCells('A3:N3');
            $sheet1->setCellValue('A3', "كشف شامل لكافة بيانات الحاويات المرفوعة ({$typeLabel}) لشهر {$monthName} {$yearName} متضمناً تفاصيل الملاحظات الرقابية");
            $sheet1->getStyle('A3')->getFont()->setBold(true)->setSize(12)->getColor()->setARGB('FFFFFFFF');
            $sheet1->getStyle('A3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF1E293B');
            $sheet1->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $sheet1->getRowDimension(3)->setRowHeight(26);

            $sheet1->mergeCells('A4:N4');
            $metaText = "تاريخ التصدير: " . date('d/m/Y H:i') . " | إجمالي الحاويات المرفوعة: " . number_format($totalCount) . " | الحاويات المطابقة: " . number_format($cleanCount) . " | الحاويات ذات الملاحظات الرقابية: " . number_format($totalAnom);
            $sheet1->setCellValue('A4', $metaText);
            $sheet1->getStyle('A4')->getFont()->setBold(true)->setSize(10)->getColor()->setARGB('FF334155');
            $sheet1->getStyle('A4')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF1F5F9');
            $sheet1->getStyle('A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $sheet1->getRowDimension(4)->setRowHeight(22);

            // Table Headers
            foreach ($headers as $col => $title) {
                $cell = $col . '6';
                $sheet1->setCellValue($cell, $title);
                $sheet1->getStyle($cell)->getFont()->setBold(true)->setSize(11)->getColor()->setARGB('FFFFFFFF');
                $sheet1->getStyle($cell)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF334155');
                $sheet1->getStyle($cell)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            }
            $sheet1->getRowDimension(6)->setRowHeight(28);

            // Bulk Insert Sheet 1 Data
            $sheet1Rows = [];
            $counter1 = 1;
            foreach ($allProcessedRows as $row) {
                $sheet1Rows[] = [
                    $counter1++,
                    $row['container_number'],
                    $row['suggested_number'] ?? '', // رقم الحاوية الصحيح (المستنتج ذكياً أو يملأ يدوياً)
                    $row['audit_details'],
                    $row['port_name'],
                    $row['entity_name'],
                    $row['size'],
                    $row['ship_name'],
                    $row['goods_type'],
                    $row['consignee'],
                    $row['arrival_date'],
                    $row['arrival_year'],
                    $row['berth'],
                    $row['original_notes'],
                ];
            }

            $totalRows1 = count($sheet1Rows);
            if ($totalRows1 > 0) {
                $sheet1->fromArray($sheet1Rows, null, 'A7');
                $endRow1 = 6 + $totalRows1;

                // Apply bulk table borders and alignments once
                $sheet1->getStyle("A7:N{$endRow1}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB('FFE2E8F0');
                $sheet1->getStyle("A7:C{$endRow1}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                $sheet1->getStyle("D7:D{$endRow1}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT)->setVertical(Alignment::VERTICAL_CENTER);
                $sheet1->getStyle("E7:E{$endRow1}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                $sheet1->getStyle("F7:F{$endRow1}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT)->setVertical(Alignment::VERTICAL_CENTER);
                $sheet1->getStyle("G7:M{$endRow1}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                $sheet1->getStyle("N7:N{$endRow1}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT)->setVertical(Alignment::VERTICAL_CENTER);

                // Highlight only rows with anomalies
                $rIdx = 7;
                foreach ($allProcessedRows as $row) {
                    if (!empty($row['has_anomaly'])) {
                        $rowBg = match ($row['severity'] ?? 'warning') {
                            'danger'  => 'FFFFF1F2',
                            'warning' => 'FFFEFCE8',
                            'info'    => 'FFF0F9FF',
                            default   => 'FFFFFBEB',
                        };
                        $sheet1->getStyle("A{$rIdx}:N{$rIdx}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($rowBg);

                        $badgeColor = match ($row['severity'] ?? 'warning') {
                            'danger'  => 'FF991B1B',
                            'warning' => 'FF92400E',
                            'info'    => 'FF0369A1',
                            default   => 'FFB45309',
                        };
                        $sheet1->getStyle("D{$rIdx}")->getFont()->setBold(true)->getColor()->setARGB($badgeColor);
                    }
                    $rIdx++;
                }
            }

            foreach ($colWidths as $col => $width) {
                $sheet1->getColumnDimension($col)->setWidth($width);
            }

            // ══════════════════════════════════════════════════════════════════
            // أوراق العمل لكل ميناء على حدة (الملاحظات الرقابية مقسمة حسب الموانئ)
            // ══════════════════════════════════════════════════════════════════
            $flaggedRows = array_values(array_filter($allProcessedRows, fn($r) => !empty($r['has_anomaly'])));
            $flaggedByPort = [];
            foreach ($flaggedRows as $row) {
                $pName = !empty($row['port_name']) ? trim($row['port_name']) : 'ميناء غير محدد';
                $flaggedByPort[$pName][] = $row;
            }

            $usedSheetTitles = ['كافة البيانات والملاحظات' => true];

            if (empty($flaggedByPort)) {
                $sheetP = $spreadsheet->createSheet();
                $sheetP->setTitle('الملاحظات الرقابية');
                $sheetP->setRightToLeft(true);
                $sheetP->setShowGridLines(true);

                $sheetP->mergeCells('A2:N2');
                $sheetP->setCellValue('A2', 'الشركة العامة لموانئ العراق - المتابعة المركزية والعمليات');
                $sheetP->getStyle('A2')->getFont()->setBold(true)->setSize(14)->getColor()->setARGB('FFFFFFFF');
                $sheetP->getStyle('A2')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF166534');
                $sheetP->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                $sheetP->getRowDimension(2)->setRowHeight(30);

                $sheetP->mergeCells('A3:N3');
                $sheetP->setCellValue('A3', "كشف الملاحظات الرقابية ({$typeLabel}) لشهر {$monthName} {$yearName} - لا توجد مخالفات أو ملاحظات مرصودة");
                $sheetP->getStyle('A3')->getFont()->setBold(true)->setSize(12)->getColor()->setARGB('FFFFFFFF');
                $sheetP->getStyle('A3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF15803D');
                $sheetP->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                $sheetP->getRowDimension(3)->setRowHeight(26);

                foreach ($colWidths as $col => $width) {
                    $sheetP->getColumnDimension($col)->setWidth($width);
                }
            } else {
                foreach ($flaggedByPort as $portNameKey => $portRows) {
                    $baseTitle = trim(preg_replace('/[\*\:\?\/\x5c\[\]]/u', '', $portNameKey));
                    if (empty($baseTitle)) $baseTitle = 'ميناء';
                    $sheetTitle = mb_substr($baseTitle, 0, 28);
                    $suffix = 1;
                    while (isset($usedSheetTitles[$sheetTitle])) {
                        $sheetTitle = mb_substr($baseTitle, 0, 24) . '_' . ($suffix++);
                    }
                    $usedSheetTitles[$sheetTitle] = true;

                    $sheetP = $spreadsheet->createSheet();
                    $sheetP->setTitle($sheetTitle);
                    $sheetP->setRightToLeft(true);
                    $sheetP->setShowGridLines(true);

                    $sheetP->mergeCells('A2:N2');
                    $sheetP->setCellValue('A2', 'الشركة العامة لموانئ العراق - المتابعة المركزية والعمليات');
                    $sheetP->getStyle('A2')->getFont()->setBold(true)->setSize(14)->getColor()->setARGB('FFFFFFFF');
                    $sheetP->getStyle('A2')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF991B1B');
                    $sheetP->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                    $sheetP->getRowDimension(2)->setRowHeight(30);

                    $sheetP->mergeCells('A3:N3');
                    $sheetP->setCellValue('A3', "كشف الحاويات التي تم رصد ملاحظات أو مخالفات رقابية عليها لميناء ({$portNameKey}) ({$typeLabel}) لشهر {$monthName} {$yearName}");
                    $sheetP->getStyle('A3')->getFont()->setBold(true)->setSize(12)->getColor()->setARGB('FFFFFFFF');
                    $sheetP->getStyle('A3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFB91C1C');
                    $sheetP->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                    $sheetP->getRowDimension(3)->setRowHeight(26);

                    $sheetP->mergeCells('A4:N4');
                    $sheetP->setCellValue('A4', "عدد الحاويات المرصودة بالميناء: " . number_format(count($portRows)) . " حاوية ذات ملاحظة رقابية | تاريخ التصدير: " . date('d/m/Y H:i'));
                    $sheetP->getStyle('A4')->getFont()->setBold(true)->setSize(10)->getColor()->setARGB('FF991B1B');
                    $sheetP->getStyle('A4')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFEE2E2');
                    $sheetP->getStyle('A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                    $sheetP->getRowDimension(4)->setRowHeight(22);

                    foreach ($headers as $col => $title) {
                        $cell = $col . '6';
                        $sheetP->setCellValue($cell, $title);
                        $sheetP->getStyle($cell)->getFont()->setBold(true)->setSize(11)->getColor()->setARGB('FFFFFFFF');
                        $sheetP->getStyle($cell)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF7F1D1D');
                        $sheetP->getStyle($cell)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                    }
                    $sheetP->getRowDimension(6)->setRowHeight(28);

                    $pSheetRows = [];
                    $pCounter = 1;
                    foreach ($portRows as $row) {
                        $pSheetRows[] = [
                            $pCounter++,
                            $row['container_number'],
                            $row['suggested_number'] ?? '', // رقم الحاوية الصحيح (المستنتج ذكياً أو يملأ يدوياً)
                            $row['audit_details'],
                            $row['port_name'],
                            $row['entity_name'],
                            $row['size'],
                            $row['ship_name'],
                            $row['goods_type'],
                            $row['consignee'],
                            $row['arrival_date'],
                            $row['arrival_year'],
                            $row['berth'],
                            $row['original_notes'],
                        ];
                    }

                    $totalPRows = count($pSheetRows);
                    if ($totalPRows > 0) {
                        $sheetP->fromArray($pSheetRows, null, 'A7');
                        $endRowP = 6 + $totalPRows;

                        // Apply bulk table borders and alignments
                        $sheetP->getStyle("A7:N{$endRowP}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB('FFE2E8F0');
                        $sheetP->getStyle("A7:C{$endRowP}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                        $sheetP->getStyle("D7:D{$endRowP}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT)->setVertical(Alignment::VERTICAL_CENTER);
                        $sheetP->getStyle("E7:E{$endRowP}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                        $sheetP->getStyle("F7:F{$endRowP}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT)->setVertical(Alignment::VERTICAL_CENTER);
                        $sheetP->getStyle("G7:M{$endRowP}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                        $sheetP->getStyle("N7:N{$endRowP}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT)->setVertical(Alignment::VERTICAL_CENTER);

                        // Highlight flagged rows
                        $rIdxP = 7;
                        foreach ($portRows as $row) {
                            $rowBg = match ($row['severity'] ?? 'warning') {
                                'danger'  => 'FFFFF1F2',
                                'warning' => 'FFFEFCE8',
                                'info'    => 'FFF0F9FF',
                                default   => 'FFFFFBEB',
                            };
                            $sheetP->getStyle("A{$rIdxP}:N{$rIdxP}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($rowBg);

                            $badgeColor = match ($row['severity'] ?? 'warning') {
                                'danger'  => 'FF991B1B',
                                'warning' => 'FF92400E',
                                'info'    => 'FF0369A1',
                                default   => 'FFB45309',
                            };
                            $sheetP->getStyle("D{$rIdxP}")->getFont()->setBold(true)->getColor()->setARGB($badgeColor);
                            $rIdxP++;
                        }
                    }

                    foreach ($colWidths as $col => $width) {
                        $sheetP->getColumnDimension($col)->setWidth($width);
                    }
                }
            }

            // ══════════════════════════════════════════════════════════════════
            // ورقة العمل 3: لوحة مؤشرات المطابقة والتدقيق الرقابي
            // ══════════════════════════════════════════════════════════════════
            $sheet3 = $spreadsheet->createSheet();
            $sheet3->setTitle('ملخص التدقيق والمطابقة');
            $sheet3->setRightToLeft(true);
            $sheet3->setShowGridLines(true);

            $sheet3->mergeCells('A2:D2');
            $sheet3->setCellValue('A2', 'الشركة العامة لموانئ العراق - المتابعة المركزية والعمليات');
            $sheet3->getStyle('A2')->getFont()->setBold(true)->setSize(13)->getColor()->setARGB('FFFFFFFF');
            $sheet3->getStyle('A2')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF0F172A');
            $sheet3->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $sheet3->getRowDimension(2)->setRowHeight(28);

            $sheet3->mergeCells('A3:D3');
            $sheet3->setCellValue('A3', "ملخص ومؤشرات التدقيق الرقابي والمطابقة لشهر {$monthName} {$yearName}");
            $sheet3->getStyle('A3')->getFont()->setBold(true)->setSize(11)->getColor()->setARGB('FFFFFFFF');
            $sheet3->getStyle('A3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF1E293B');
            $sheet3->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $sheet3->getRowDimension(3)->setRowHeight(24);

            $summaryItems = [
                ['المؤشر الرقابي', 'العدد', 'النسبة من الإجمالي', 'الحالة / التقييم'],
                ['إجمالي الحاويات المفحوصة والمستوردة', $totalCount, '100%', 'مكتمل'],
                ['الحاويات المطابقة تماماً للنظام والمواصفات', $cleanCount, $totalCount > 0 ? round(($cleanCount / $totalCount) * 100, 1) . '%' : '0%', 'مطابق'],
                ['الحاويات بدون تاريخ أو سنة وصول (مفقودة)', count($stats['missing_date_anomalies']), $totalCount > 0 ? round((count($stats['missing_date_anomalies']) / $totalCount) * 100, 1) . '%' : '0%', 'تنبيه (مدرجة 2015 فما دون)'],
                ['الحاويات المخالفة للمواصفة القياسية ISO 6346', count($stats['invalid_iso_anomalies']), $totalCount > 0 ? round((count($stats['invalid_iso_anomalies']) / $totalCount) * 100, 1) . '%' : '0%', 'مخالفة معيارية'],
                ['الحاويات التي تم تغيير مينائها عن السجلات السابقة', count($stats['port_change_anomalies']), $totalCount > 0 ? round((count($stats['port_change_anomalies']) / $totalCount) * 100, 1) . '%' : '0%', 'نقل ميناء'],
                ['الحاويات التي اختلف تاريخ وصولها عن السابق', count($stats['date_mismatch_anomalies']), $totalCount > 0 ? round((count($stats['date_mismatch_anomalies']) / $totalCount) * 100, 1) . '%' : '0%', 'تحديث تاريخ'],
                ['الحاويات المضافة بسنة سابقة لم تكن مسجلة', count($stats['prior_year_anomalies']), $totalCount > 0 ? round((count($stats['prior_year_anomalies']) / $totalCount) * 100, 1) . '%' : '0%', 'ملاحظة رقابية'],
                ['إجمالي الحاويات المخرجة لهذا الشهر', $stats['discharged_count'], '-', 'تم التخريج بنجاح'],
            ];

            $sRow = 5;
            foreach ($summaryItems as $idx => $sItem) {
                $sheet3->setCellValue('A' . $sRow, $sItem[0]);
                $sheet3->setCellValue('B' . $sRow, $sItem[1]);
                $sheet3->setCellValue('C' . $sRow, $sItem[2]);
                $sheet3->setCellValue('D' . $sRow, $sItem[3]);

                if ($idx === 0) {
                    $sheet3->getStyle("A{$sRow}:D{$sRow}")->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
                    $sheet3->getStyle("A{$sRow}:D{$sRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF334155');
                } else {
                    $bg = ($sRow % 2 === 0) ? 'FFF8FAFC' : 'FFFFFFFF';
                    $sheet3->getStyle("A{$sRow}:D{$sRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($bg);
                }

                $sheet3->getStyle("A{$sRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT)->setVertical(Alignment::VERTICAL_CENTER);
                $sheet3->getStyle("B{$sRow}:D{$sRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                $sheet3->getStyle("A{$sRow}:D{$sRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB('FFE2E8F0');
                $sheet3->getRowDimension($sRow)->setRowHeight(24);
                $sRow++;
            }

            $sheet3->getColumnDimension('A')->setWidth(48);
            $sheet3->getColumnDimension('B')->setWidth(16);
            $sheet3->getColumnDimension('C')->setWidth(20);
            $sheet3->getColumnDimension('D')->setWidth(28);

            // Return to Sheet 1 as active
            $spreadsheet->setActiveSheetIndex(0);

            $storageDir = storage_path('app/public/audit_reports');
            if (!is_dir($storageDir)) {
                mkdir($storageDir, 0777, true);
            }

            $safeMonth = $month ? $month->month_number : date('m');
            $safeYear = $fiscalYear ? $fiscalYear->year : date('Y');
            $fileName = "كشف_الملاحظات_الرقابية_{$typeLabel}_{$safeYear}_{$safeMonth}_" . date('Ymd_His') . ".xlsx";
            $fullPath = $storageDir . DIRECTORY_SEPARATOR . $fileName;

            $writer = new Xlsx($spreadsheet);
            $writer->save($fullPath);

            return [
                'file_path'    => $fullPath,
                'file_name'    => $fileName,
                'download_url' => asset('storage/audit_reports/' . $fileName),
            ];
        } catch (\Throwable $e) {
            Log::error('Failed to generate audit Excel report: ' . $e->getMessage());
            return null;
        }
    }
}
