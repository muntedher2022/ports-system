<?php

namespace App\Helpers;

use Illuminate\Database\Eloquent\Builder;

class ArabicSearchHelper
{
    /**
     * Normalize Arabic string in PHP (remove alef hamzas, maddas, taa marbuta, etc.)
     */
    public static function normalize(?string $text): string
    {
        if ($text === null) {
            return '';
        }

        $patterns = [
            '/[أإآٱ]/u' => 'ا',
            '/[ة]/u'     => 'ه',
            '/[ى]/u'     => 'ي',
            '/[\x{064B}-\x{065F}]/u' => '', // Tashkeel / Harakat
        ];

        return preg_replace(array_keys($patterns), array_values($patterns), trim($text));
    }

    /**
     * Return SQL expression to normalize a column in MySQL / MariaDB
     */
    public static function sqlColumn(string $column): string
    {
        return "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE({$column}, 'أ', 'ا'), 'إ', 'ا'), 'آ', 'ا'), 'ة', 'ه'), 'ى', 'ي')";
    }

    /**
     * Apply normalized Arabic search on an Eloquent builder
     */
    public static function applySearch(Builder $query, string $column, string $search): Builder
    {
        $normalizedSearch = self::normalize($search);
        $sql = self::sqlColumn($column);

        return $query->whereRaw("{$sql} LIKE ?", ["%{$normalizedSearch}%"]);
    }
}
