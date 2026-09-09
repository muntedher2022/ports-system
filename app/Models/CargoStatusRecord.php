<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Contracts\Auditable;

class CargoStatusRecord extends Model implements Auditable
{
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'port_id',
        'fiscal_year_id',
        'month_id',
        'report_date',
        'cargo_type',
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
        return $this->hasMany(CargoStatusDetail::class);
    }

    public function getCargoTypeLabelAttribute(): string
    {
        return match($this->cargo_type) {
            'abandoned' => 'مواد وبضائع متخلفة',
            'dangerous' => 'مواد وبضائع خطرة',
            default     => $this->cargo_type,
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

    /** إجمالي المواد والبضائع في هذا التقرير */
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
