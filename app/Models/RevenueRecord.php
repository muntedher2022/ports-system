<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class RevenueRecord extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;

    protected $fillable = [
        'revenue_center_id', 'fiscal_year_id', 'month_id',
        'gross_revenue', 'net_revenue',
        'status', 'reopen_reason',
        'created_by', 'submitted_by', 'approved_by',
        'submitted_at', 'approved_at',
    ];

    protected $casts = [
        'gross_revenue' => 'decimal:3',
        'net_revenue' => 'decimal:3',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
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

    protected static function booted(): void
    {
        static::saved(function (RevenueRecord $record) {
            $record->syncToMonthlyPortRecord();
        });

        static::restored(function (RevenueRecord $record) {
            $record->syncToMonthlyPortRecord();
        });

        static::deleted(function (RevenueRecord $record) {
            $portId = RevenueCenter::where('id', $record->revenue_center_id)->value('port_id');
            if ($portId) {
                MonthlyPortRecord::where('port_id', $portId)
                    ->where('fiscal_year_id', $record->fiscal_year_id)
                    ->where('month_id', $record->month_id)
                    ->update(['total_revenue' => 0]);
            }
        });
    }

    /**
     * مزامنة الإيراد الكلي تلقائياً مع السجل التشغيلي للميناء لنفس الشهر والسنة
     */
    public function syncToMonthlyPortRecord(): void
    {
        $portId = RevenueCenter::where('id', $this->revenue_center_id)->value('port_id');
        if (! $portId || ! $this->fiscal_year_id || ! $this->month_id) {
            return;
        }

        MonthlyPortRecord::where('port_id', $portId)
            ->where('fiscal_year_id', $this->fiscal_year_id)
            ->where('month_id', $this->month_id)
            ->update(['total_revenue' => (float) $this->gross_revenue]);
    }
}
