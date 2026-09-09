<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContainerMovement extends Model
{
    protected $fillable = [
        'monthly_port_record_id', 'direction', 'full_or_empty',
        'size_20ft', 'size_40ft', 'size_45ft', 'teu_total', 'weight_tons',
    ];

    protected $casts = [
        'weight_tons' => 'decimal:3',
    ];

    public function monthlyPortRecord(): BelongsTo
    {
        return $this->belongsTo(MonthlyPortRecord::class);
    }
}
