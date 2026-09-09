<?php

namespace App\Imports;

use App\Models\MonthlyPortRecord;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class PortRecordImport implements
    ToModel,
    WithHeadingRow,
    SkipsEmptyRows,
    WithBatchInserts,
    WithChunkReading
{
    public int $importedCount = 0;
    public int $updatedCount  = 0;
    public array $failures    = [];

    public function model(array $row): ?MonthlyPortRecord
    {
        $portId       = (int) $this->extract($row, 'port_id');
        $fiscalYearId = (int) $this->extract($row, 'fiscal_year_id');
        $monthId      = (int) $this->extract($row, 'month_id');

        if (!$portId || !$fiscalYearId || !$monthId) {
            return null;
        }

        // الحاويات المستوردة
        $imp20 = $this->int($row, 'imported_20ft');
        $imp40 = $this->int($row, 'imported_40ft');
        $imp45 = $this->int($row, 'imported_45ft');
        $calculatedImpCount = $imp20 + $imp40 + $imp45;
        $calculatedImpTeu = $imp20 + ($imp40 * 2) + ($imp45 * 2);

        // الحاويات المصدرة
        $expEmpty = $this->int($row, 'exported_empty_count');
        $expFull  = $this->int($row, 'exported_full_count');
        $calculatedExpCount = $expEmpty + $expFull;

        $exp20 = $this->int($row, 'exported_20ft');
        $exp40 = $this->int($row, 'exported_40ft');
        $exp45 = $this->int($row, 'exported_45ft');
        $calculatedExpTeu = $exp20 + ($exp40 * 2) + ($exp45 * 2);

        // المشتقات النفطية
        $oilExported = $this->dec($row, 'oil_exported_tons');
        $oilImported = $this->dec($row, 'oil_imported_tons');
        $calculatedOilTotal = $oilExported + $oilImported;

        $existing = MonthlyPortRecord::withTrashed()->where([
            'port_id'        => $portId,
            'fiscal_year_id' => $fiscalYearId,
            'month_id'       => $monthId,
        ])->first();

        // الإيراد المالي: إذا تُرِك 0 أو فارغاً، يتم جلبه تلقائياً من سجلات الإيراد المالي للميناء
        $revenue = $this->dec($row, 'total_revenue');
        if ($revenue <= 0) {
            $revenueCenter = \App\Models\RevenueCenter::where('port_id', $portId)->first();
            if ($revenueCenter) {
                $revRecord = \App\Models\RevenueRecord::where([
                    'revenue_center_id' => $revenueCenter->id,
                    'fiscal_year_id'    => $fiscalYearId,
                    'month_id'          => $monthId,
                ])->first();
                if ($revRecord) {
                    $revenue = (float) $revRecord->gross_revenue;
                }
            }
        }

        $data = [
            'port_id'                          => $portId,
            'fiscal_year_id'                   => $fiscalYearId,
            'month_id'                         => $monthId,

            // 1. حركة البواخر والسيارات
            'total_container_ships'            => $this->int($row, 'total_container_ships'),
            'general_cargo_ships'              => $this->int($row, 'general_cargo_ships'),
            'oil_tankers_count'                => $this->int($row, 'oil_tankers_count'),
            'car_carrier_ships'                => $this->int($row, 'car_carrier_ships'),
            'imported_cars_count'              => $this->int($row, 'imported_cars_count'),
            'imported_cars_weight_tons'        => $this->dec($row, 'imported_cars_weight_tons'),

            // 2. الحاويات المستوردة
            'imported_containers_weight_tons'  => $this->dec($row, 'imported_containers_weight_tons'),
            'imported_containers_count'        => ($this->int($row, 'imported_containers_count') > 0) ? $this->int($row, 'imported_containers_count') : $calculatedImpCount,
            'imported_20ft'                    => $imp20,
            'imported_40ft'                    => $imp40,
            'imported_45ft'                    => $imp45,
            'imported_teu'                     => $calculatedImpTeu, // محسوب تلقائياً

            // 3. الحاويات المصدرة
            'exported_empty_count'             => $expEmpty,
            'exported_full_count'              => $expFull,
            'exported_full_weight_tons'        => $this->dec($row, 'exported_full_weight_tons'),
            'exported_containers_count'        => $calculatedExpCount, // محسوب تلقائياً
            'exported_20ft'                    => $exp20,
            'exported_40ft'                    => $exp40,
            'exported_45ft'                    => $exp45,
            'exported_teu'                     => $calculatedExpTeu, // محسوب تلقائياً

            // 4. البضائع العامة والمتنوعة
            'general_cargo_weight_tons'        => $this->dec($row, 'general_cargo_weight_tons'),

            // 5. النفط والمشتقات النفطية
            'oil_exported_tons'                => $oilExported,
            'oil_imported_tons'                => $oilImported,
            'oil_total_tons'                   => $calculatedOilTotal, // محسوب تلقائياً

            // 6. الإيراد المالي
            'total_revenue'                    => $revenue,
            'status'                           => 'approved',
            'created_by'                       => Auth::id() ?? 1,
        ];

        if ($existing) {
            $existing->restore();
            $existing->fill($data);
            $existing->save();
            $this->updatedCount++;
            return null;
        }

        $this->importedCount++;
        return new MonthlyPortRecord($data);
    }

    public function batchSize(): int { return 100; }
    public function chunkSize(): int { return 100; }

    private function extract(array $row, string $key): mixed
    {
        foreach ($row as $col => $val) {
            $cleanCol = (string) $col;
            if (str_starts_with($cleanCol, $key) || str_contains($cleanCol, $key)) {
                if (is_string($val)) {
                    $val = str_replace([',', ' '], '', trim($val));
                }
                return $val;
            }
        }
        return null;
    }

    private function int(array $row, string $key): int
    {
        return (int) ($this->extract($row, $key) ?? 0);
    }

    private function dec(array $row, string $key): float
    {
        return (float) ($this->extract($row, $key) ?? 0);
    }
}
