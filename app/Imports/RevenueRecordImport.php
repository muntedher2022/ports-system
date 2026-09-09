<?php

namespace App\Imports;

use App\Models\RevenueRecord;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class RevenueRecordImport implements
    ToModel,
    WithHeadingRow,
    SkipsEmptyRows,
    WithBatchInserts,
    WithChunkReading
{
    public int $importedCount = 0;
    public int $updatedCount = 0;
    public array $failures = [];

    public function model(array $row): ?RevenueRecord
    {
        $centerId     = (int) $this->extract($row, 'revenue_center_id');
        $fiscalYearId = (int) $this->extract($row, 'fiscal_year_id');
        $monthId      = (int) $this->extract($row, 'month_id');

        // إذا كان الصف فارغاً
        if (!$centerId || !$fiscalYearId || !$monthId) {
            return null;
        }

        $gross = (float) ($this->extract($row, 'gross_revenue') ?? 0);
        $net   = (float) ($this->extract($row, 'net_revenue') ?? 0);

        $existing = RevenueRecord::withTrashed()->where([
            'revenue_center_id' => $centerId,
            'fiscal_year_id'    => $fiscalYearId,
            'month_id'          => $monthId,
        ])->first();

        // منع تكرار الصافي: إذا تم وضع نفس الصافي الكلي في كل صفوف الشهر، نحفظه لمركز واحد فقط (مركز 1) والبقية 0
        static $seenNetMonths = [];
        $monthKey = $fiscalYearId . '_' . $monthId;
        $assignedNet = $net;
        if ($net > 0) {
            if (isset($seenNetMonths[$monthKey])) {
                $assignedNet = 0;
            } else {
                $seenNetMonths[$monthKey] = true;
            }
        }

        $data = [
            'revenue_center_id' => $centerId,
            'fiscal_year_id'    => $fiscalYearId,
            'month_id'          => $monthId,
            'gross_revenue'     => $gross,
            'net_revenue'       => $assignedNet,
            'status'            => 'approved',
            'created_by'        => Auth::id() ?? 1,
        ];

        if ($existing) {
            $existing->restore();
            $existing->fill($data);
            $existing->save();
            $this->updatedCount++;
            return null;
        }

        $this->importedCount++;
        return new RevenueRecord($data);
    }

    public function batchSize(): int
    {
        return 200;
    }

    public function chunkSize(): int
    {
        return 200;
    }

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
}
