<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Contracts\Auditable;

class CargoStatusDetail extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'cargo_status_record_id',
        'cargo_entity_id',
        'year_label',
        'count',
        'sort_order',
    ];

    protected $casts = [
        'year_label' => 'integer',
        'count'      => 'integer',
        'sort_order' => 'integer',
    ];

    public function record(): BelongsTo
    {
        return $this->belongsTo(CargoStatusRecord::class, 'cargo_status_record_id');
    }

    public function entity(): BelongsTo
    {
        return $this->belongsTo(CargoEntity::class, 'cargo_entity_id');
    }
}
