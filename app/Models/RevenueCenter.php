<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class RevenueCenter extends Model implements Auditable
{
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;
    protected $fillable = [
        'name_ar', 'code', 'port_id',
        'is_operational', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'is_operational' => 'boolean',
        'is_active'      => 'boolean',
    ];

    public function port(): BelongsTo
    {
        return $this->belongsTo(Port::class);
    }

    public function revenueRecords(): HasMany
    {
        return $this->hasMany(RevenueRecord::class);
    }
}
