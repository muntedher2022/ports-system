<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OilProduct extends Model
{
    protected $fillable = [
        'monthly_port_record_id', 'direction', 'weight_tons',
    ];

    protected $casts = [
        'weight_tons' => 'decimal:3',
    ];

    public function monthlyPortRecord(): BelongsTo
    {
        return $this->belongsTo(MonthlyPortRecord::class);
    }
}
