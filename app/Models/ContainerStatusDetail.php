<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Contracts\Auditable;

class ContainerStatusDetail extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    protected $fillable = [
        'container_status_record_id',
        'container_entity_id',
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
        return $this->belongsTo(ContainerStatusRecord::class, 'container_status_record_id');
    }

    public function entity(): BelongsTo
    {
        return $this->belongsTo(ContainerEntity::class, 'container_entity_id');
    }
}
