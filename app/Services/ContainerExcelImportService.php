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
        $ports = Port::all();

        foreach ($ports as $p) {
            if (static::normalizeArabic($p->name_ar) === $clean) {
                return $p;
            }
        }

        foreach ($ports as $p) {
            $pNorm = static::normalizeArabic($p->name_ar);
            $coreP = trim(str_replace(['ميناء', 'ال'], '', $pNorm));
            $coreSh = trim(str_replace(['ميناء', 'ال'], '', $clean));
            if (!empty($coreP) && (str_contains($clean, $coreP) || str_contains($pNorm, $coreSh))) {
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

                // Parse arrival date & arrival year (Default is 2015 when empty)
                $arrivalDate = null;
                $arrivalYear = '2015';

                if (is_numeric($rawDate)) {
                    try {
                        $dt = ExcelDate::excelToDateTimeObject($rawDate);
                        $arrivalDate = $dt->format('Y-m-d');
                        $arrivalYear = $dt->format('Y');
                    } catch (\Throwable $e) {
                        $arrivalYear = '2015';
                    }
                } elseif (is_string($rawDate) && !empty($rawDate)) {
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
                    $arrivalYear = '2015';
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
            'prior_year_anomalies'    => [],
            'date_mismatch_anomalies' => [],
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

                // 3. Check for Anomalies (اختلاف تاريخ الوصول عن السابق + مخالفة إضافة حاويات بسنوات سابقة)
                $prevItemsByNumber = $prevItems->keyBy('container_number');

                foreach ($items as &$itm) {
                    $cNo = $itm['container_number'];
                    $arrYear = trim((string) $itm['arrival_year']);
                    $currDate = $itm['arrival_date'];

                    // A. Check for Date Mismatch against previous records
                    $prevContainer = $prevItemsByNumber->get($cNo)
                        ?? ContainerItem::where('container_number', $cNo)
                            ->where('port_id', $portId)
                            ->where(function ($q) use ($fiscalYearId, $monthId) {
                                $q->where('fiscal_year_id', '<', $fiscalYearId)
                                  ->orWhere(function ($q2) use ($fiscalYearId, $monthId) {
                                      $q2->where('fiscal_year_id', $fiscalYearId)
                                         ->where('month_id', '<', $monthId);
                                  });
                            })
                            ->latest('id')
                            ->first();

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
                            $entityName = ContainerEntity::find($itm['container_entity_id'])?->name_ar ?? '';
                            $stats['date_mismatch_anomalies'][] = [
                                'port_name'        => $portName,
                                'container_number' => $cNo,
                                'prev_date'        => (string)$prevDateLabel,
                                'curr_date'        => (string)$currDateLabel,
                                'entity_name'      => $entityName,
                            ];
                            $warnText = "[تنبيه رقابي: اختلاف تاريخ الوصول عن السجل السابق (السابق: {$prevDateLabel}، الحالي: {$currDateLabel})]";
                            $itm['notes'] = !empty($itm['notes']) ? ($itm['notes'] . ' | ' . $warnText) : $warnText;
                        }
                    }

                    // B. Check for Prior-Year Additions Anomaly
                    if (!empty($prevNumbersList)) {
                        $isPriorYear = is_numeric($arrYear) && (int) $arrYear < $currentYearNum;
                        if ($isPriorYear && !in_array($cNo, $prevNumbersList)) {
                            $entityName = ContainerEntity::find($itm['container_entity_id'])?->name_ar ?? '';
                            $stats['prior_year_anomalies'][] = [
                                'port_name'        => $portName,
                                'container_number' => $cNo,
                                'arrival_year'     => $arrYear,
                                'entity_name'      => $entityName,
                            ];
                            $warnPrior = "[تنبيه رقابي: حاوية بسنة سابقة ({$arrYear}) مضافة حديثاً لم تكن مسجلة في الشهر السابق]";
                            $itm['notes'] = !empty($itm['notes']) ? ($itm['notes'] . ' | ' . $warnPrior) : $warnPrior;
                        }
                    }
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

        // Log the import activity with lightweight stats
        $auditStats = [
            'total_imported'             => $stats['total_imported'],
            'discharged_count'           => $stats['discharged_count'],
            'prior_year_anomalies_count' => count($stats['prior_year_anomalies']),
            'date_mismatch_count'        => count($stats['date_mismatch_anomalies']),
            'ports_processed'            => $stats['ports_processed'],
        ];

        ActivityLogger::log(
            'created',
            "استيراد ومعالجة ذكية لموقف الحاويات (" . ($containerType === 'dangerous' ? 'الخطرة' : 'المتخلفة') . ") لشهر {$currMonth?->name_ar} {$fiscalYearModel?->year} - إجمالي {$stats['total_imported']} حاوية ومقاطعة {$stats['discharged_count']} حاوية مخرجة",
            ContainerStatusRecord::class,
            $stats['ports_processed'] ? reset($stats['ports_processed'])['record_id'] : null,
            $auditStats
        );

        return $stats;
    }
}
