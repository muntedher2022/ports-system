<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class FiscalYear extends Model implements Auditable
{
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;
    protected $fillable = ['year', 'is_current'];

    protected $casts = [
        'is_current' => 'boolean',
        'year'       => 'integer',
    ];

    public function monthlyPortRecords(): HasMany
    {
        return $this->hasMany(MonthlyPortRecord::class);
    }

    public function revenueRecords(): HasMany
    {
        return $this->hasMany(RevenueRecord::class);
    }

    public static function current(): ?self
    {
        return static::where('is_current', true)->first();
    }
}
