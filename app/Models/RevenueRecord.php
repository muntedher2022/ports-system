<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class RevenueRecord extends Model implements Auditable
{
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'revenue_center_id', 'fiscal_year_id', 'month_id',
        'gross_revenue', 'net_revenue',
        'status', 'reopen_reason',
        'created_by', 'submitted_by', 'approved_by',
        'submitted_at', 'approved_at',
    ];

    protected $casts = [
        'gross_revenue' => 'decimal:3',
        'net_revenue'   => 'decimal:3',
        'submitted_at'  => 'datetime',
        'approved_at'   => 'datetime',
    ];

    protected $auditInclude = [
        'gross_revenue', 'net_revenue', 'status',
    ];

    public function revenueCenter(): BelongsTo
    {
        return $this->belongsTo(RevenueCenter::class);
    }

    public function fiscalYear(): BelongsTo
    {
        return $this->belongsTo(FiscalYear::class);
    }

    public function month(): BelongsTo
    {
        return $this->belongsTo(Month::class);
    }

    public function isLocked(): bool
    {
        return in_array($this->status, ['approved', 'locked']);
    }
}
