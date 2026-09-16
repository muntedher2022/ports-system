<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class ContainerItem extends Model implements Auditable
{
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'container_status_record_id',
        'port_id',
        'container_entity_id',
        'fiscal_year_id',
        'month_id',
        'container_type',
        'container_number',
        'size',
        'ship_name',
        'goods_type',
        'consignee',
        'arrival_date',
        'arrival_year',
        'berth',
        'status',
        'discharge_fiscal_year_id',
        'discharge_month_id',
        'discharge_date',
        'is_manually_added',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'arrival_date'      => 'date',
        'discharge_date'   => 'date',
        'is_manually_added' => 'boolean',
    ];

    public function record(): BelongsTo
    {
        return $this->belongsTo(ContainerStatusRecord::class, 'container_status_record_id');
    }

    public function port(): BelongsTo
    {
        return $this->belongsTo(Port::class, 'port_id');
    }

    public function entity(): BelongsTo
    {
        return $this->belongsTo(ContainerEntity::class, 'container_entity_id');
    }

    public function fiscalYear(): BelongsTo
    {
        return $this->belongsTo(FiscalYear::class, 'fiscal_year_id');
    }

    public function month(): BelongsTo
    {
        return $this->belongsTo(Month::class, 'month_id');
    }

    public function dischargeFiscalYear(): BelongsTo
    {
        return $this->belongsTo(FiscalYear::class, 'discharge_fiscal_year_id');
    }

    public function dischargeMonth(): BelongsTo
    {
        return $this->belongsTo(Month::class, 'discharge_month_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Scopes
    public function scopeInPort(Builder $query): Builder
    {
        return $query->where('status', 'in_port');
    }

    public function scopeDischarged(Builder $query): Builder
    {
        return $query->where('status', 'discharged');
    }

    // Status Label & Color
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'discharged' => 'تم اخراجها',
            'in_port'    => 'موجودة في الميناء',
            default      => 'موجودة في الميناء',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'discharged' => 'success',
            'in_port'    => 'warning',
            default      => 'warning',
        };
    }

    public function getContainerTypeLabelAttribute(): string
    {
        return $this->container_type === 'dangerous' ? 'حاويات خطرة' : 'حاويات متخلفة';
    }

    /**
     * تحديث المجاميع والإحصائيات التلقائية في جدول التفاصيل
     */
    public static function syncRecordDetails(int $recordId): void
    {
        $record = ContainerStatusRecord::find($recordId);
        if (!$record) return;

        // Group container items by entity and arrival year
        $aggregates = static::where('container_status_record_id', $recordId)
            ->where('status', 'in_port')
            ->selectRaw('container_entity_id, arrival_year, COUNT(*) as total_count')
            ->groupBy('container_entity_id', 'arrival_year')
            ->get();

        // Remove previous computed details for this record
        $record->details()->delete();

        // Accumulate years <= 2015 into 2015
        $grouped = [];
        foreach ($aggregates as $agg) {
            $year = trim((string) $agg->arrival_year);
            if (empty($year) || (is_numeric($year) && (int) $year <= 2015)) {
                $targetYear = '2015';
            } else {
                $targetYear = $year;
            }

            $eid = $agg->container_entity_id;
            if (!isset($grouped[$eid][$targetYear])) {
                $grouped[$eid][$targetYear] = 0;
            }
            $grouped[$eid][$targetYear] += (int) $agg->total_count;
        }

        $sortOrder = 1;
        $totalRecordCount = 0;

        foreach ($grouped as $eid => $yearCounts) {
            foreach ($yearCounts as $yearLabel => $count) {
                if ($count <= 0) continue;
                ContainerStatusDetail::create([
                    'container_status_record_id' => $record->id,
                    'container_entity_id'        => $eid,
                    'year_label'                 => (string) $yearLabel,
                    'count'                      => (int) $count,
                    'sort_order'                 => $sortOrder++,
                ]);
                $totalRecordCount += (int) $count;
            }
        }

        $record->update(['total_count' => $totalRecordCount]);
    }
}
