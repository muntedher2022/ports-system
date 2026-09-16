<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Contracts\Auditable;

class ContainerStatusRecord extends Model implements Auditable
{
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'port_id',
        'fiscal_year_id',
        'month_id',
        'report_date',
        'container_type',
        'notes',
        'excel_file_path',
        'excel_file_name',
        'excel_file_size',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'report_date' => 'date',
    ];

    protected static function booted(): void
    {
        static::deleting(function (ContainerStatusRecord $record) {
            if ($record->isForceDeleting()) {
                // 1. Permanently delete associated details
                $record->details()->forceDelete();

                // 2. Permanently delete container items belonging only to this record
                ContainerItem::withTrashed()
                    ->where(function ($q) use ($record) {
                        $q->where('container_status_record_id', $record->id)
                          ->orWhere(function ($q2) use ($record) {
                              $q2->where('port_id', $record->port_id)
                                 ->where('fiscal_year_id', $record->fiscal_year_id)
                                 ->where('month_id', $record->month_id)
                                 ->where('container_type', $record->container_type);
                          });
                    })
                    ->forceDelete();
            } else {
                // Soft delete associated details and items
                $record->details()->delete();

                ContainerItem::where(function ($q) use ($record) {
                    $q->where('container_status_record_id', $record->id)
                      ->orWhere(function ($q2) use ($record) {
                          $q2->where('port_id', $record->port_id)
                             ->where('fiscal_year_id', $record->fiscal_year_id)
                             ->where('month_id', $record->month_id)
                             ->where('container_type', $record->container_type);
                      });
                })->delete();
            }

            // Reset any containers discharged during this record's month back to active 'in_port'
            ContainerItem::where('discharge_fiscal_year_id', $record->fiscal_year_id)
                ->where('discharge_month_id', $record->month_id)
                ->where('port_id', $record->port_id)
                ->where('container_type', $record->container_type)
                ->update([
                    'status'                   => 'in_port',
                    'discharge_fiscal_year_id' => null,
                    'discharge_month_id'       => null,
                    'discharge_date'           => null,
                ]);
        });

        static::restoring(function (ContainerStatusRecord $record) {
            // Restore associated container items
            ContainerItem::withTrashed()
                ->where('container_status_record_id', $record->id)
                ->restore();

            // Re-sync and reconstruct details from restored items
            ContainerItem::syncRecordDetails($record->id);
        });
    }

    public function port(): BelongsTo
    {
        return $this->belongsTo(Port::class);
    }

    public function fiscalYear(): BelongsTo
    {
        return $this->belongsTo(FiscalYear::class);
    }

    public function month(): BelongsTo
    {
        return $this->belongsTo(Month::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function details(): HasMany
    {
        return $this->hasMany(ContainerStatusDetail::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ContainerItem::class);
    }

    public function getContainerTypeLabelAttribute(): string
    {
        return match($this->container_type) {
            'abandoned' => 'حاويات متخلفة',
            'dangerous' => 'حاويات خطرة',
            default     => $this->container_type,
        };
    }

    public function getMonthNameAttribute(): string
    {
        return $this->month?->name_ar ?? '';
    }

    public function getMonthNumberAttribute(): int
    {
        return $this->month?->month_number ?? 0;
    }

    /** إجمالي الحاويات في هذا التقرير */
    public function getTotalCountAttribute(): int
    {
        return $this->details()->sum('count');
    }

    /** إجمالي القطاع الحكومي */
    public function getGovernmentTotalAttribute(): int
    {
        return $this->details()
            ->whereHas('entity', fn($q) => $q->where('entity_type', 'government'))
            ->sum('count');
    }

    /** إجمالي القطاع الخاص */
    public function getPrivateTotalAttribute(): int
    {
        return $this->details()
            ->whereHas('entity', fn($q) => $q->where('entity_type', 'private'))
            ->sum('count');
    }
}
