<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class Port extends Model implements Auditable
{
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;
    protected $fillable = [
        'name_ar', 'code', 'type',
        'has_monthly_records', 'has_container_status', 'has_cargo_status',
        'has_containers', 'has_oil', 'has_cars',
        'is_active', 'sort_order',
    ];

    protected $casts = [
        'has_monthly_records'  => 'boolean',
        'has_container_status' => 'boolean',
        'has_cargo_status'     => 'boolean',
        'has_containers'       => 'boolean',
        'has_oil'              => 'boolean',
        'has_cars'             => 'boolean',
        'is_active'            => 'boolean',
    ];

    public function monthlyPortRecords(): HasMany
    {
        return $this->hasMany(MonthlyPortRecord::class);
    }

    public function revenueCenters(): HasMany
    {
        return $this->hasMany(RevenueCenter::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
