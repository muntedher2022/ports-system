<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $primaryKey = 'key';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'key',
        'value',
        'group',
    ];

    /**
     * الحصول على قيمة إعداد مع كاش فوري
     */
    public static function get(string $key, $default = null): ?string
    {
        return Cache::remember("setting_{$key}", 3600, function () use ($key, $default) {
            $setting = static::find($key);
            return $setting !== null ? $setting->value : $default;
        });
    }

    /**
     * حفظ أو تحديث قيمة إعداد مع إفراغ الكاش
     */
    public static function set(string $key, ?string $value, string $group = 'general'): static
    {
        $setting = static::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => $group]
        );

        Cache::forget("setting_{$key}");

        return $setting;
    }
}
