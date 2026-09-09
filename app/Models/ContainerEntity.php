<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Contracts\Auditable;

class ContainerEntity extends Model implements Auditable
{
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'name_ar',
        'entity_type',
        'sort_order',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function details(): HasMany
    {
        return $this->hasMany(ContainerStatusDetail::class);
    }

    public function getEntityTypeLabelAttribute(): string
    {
        return match($this->entity_type) {
            'government' => 'قطاع حكومي',
            'private'    => 'قطاع خاص',
            default      => $this->entity_type,
        };
    }

    public function scopeGovernment($query)
    {
        return $query->where('entity_type', 'government');
    }

    public function scopePrivate($query)
    {
        return $query->where('entity_type', 'private');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
