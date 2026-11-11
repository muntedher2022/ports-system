<?php

namespace App\Helpers;

class ArabicTafqeetHelper
{
    private static array $ones = [
        0 => '',
        1 => 'واحد',
        2 => 'اثنان',
        3 => 'ثلاثة',
        4 => 'أربعة',
        5 => 'خمسة',
        6 => 'ستة',
        7 => 'سبعة',
        8 => 'ثمانية',
        9 => 'تسعة',
        10 => 'عشرة',
        11 => 'أحد عشر',
        12 => 'اثنا عشر',
        13 => 'ثلاثة عشر',
        14 => 'أربعة عشر',
        15 => 'خمسة عشر',
        16 => 'ستة عشر',
        17 => 'سبعة عشر',
        18 => 'ثمانية عشر',
        19 => 'تسعة عشر',
    ];

    private static array $tens = [
        2 => 'عشرون',
        3 => 'ثلاثون',
        4 => 'أربعون',
        5 => 'خمسون',
        6 => 'ستون',
        7 => 'سبعون',
        8 => 'ثمانون',
        9 => 'تسعون',
    ];

    private static array $hundreds = [
        1 => 'مائة',
        2 => 'مائتان',
        3 => 'ثلاثمائة',
        4 => 'أربعمائة',
        5 => 'خمسمائة',
        6 => 'ستمائة',
        7 => 'سبعمائة',
        8 => 'ثمانمائة',
        9 => 'تسعمائة',
    ];

    /**
     * تحويل مبلغ مالي إلى تفقيط عربي رسمي بالدينار العراقي
     */
    public static function toIraqiDinars(mixed $amount): string
    {
        $clean = is_string($amount) ? str_replace([',', ' '], '', $amount) : $amount;
        if (! is_numeric($clean)) {
            return 'صفر دينار عراقي';
        }

        $num = (int) round((float) $clean);
        if ($num <= 0) {
            return 'صفر دينار عراقي';
        }

        $words = self::convertNumber($num);

        return "فقط {$words} دينار عراقي لا غير";
    }

    /**
     * تحويل عدد صحيح موجب إلى كلمات عربية
     */
    public static function convertNumber(int $number): string
    {
        if ($number === 0) {
            return 'صفر';
        }

        $scales = [
            1000000000000 => ['تريليون', 'تريليونان', 'تريليونات', 'تريليوناً'],
            1000000000    => ['مليار', 'ملياران', 'مليارات', 'ملياراً'],
            1000000       => ['مليون', 'مليونان', 'ملايين', 'مليوناً'],
            1000          => ['ألف', 'ألفان', 'آلاف', 'ألفاً'],
        ];

        $parts = [];
        $remainder = $number;

        foreach ($scales as $scaleValue => $scaleNames) {
            if ($remainder >= $scaleValue) {
                $group = intdiv($remainder, $scaleValue);
                $remainder %= $scaleValue;

                if ($group === 1) {
                    $parts[] = $scaleNames[0];
                } elseif ($group === 2) {
                    $parts[] = $scaleNames[1];
                } elseif ($group >= 3 && $group <= 10) {
                    $parts[] = self::convertThreeDigits($group).' '.$scaleNames[2];
                } else {
                    $parts[] = self::convertThreeDigits($group).' '.$scaleNames[0];
                }
            }
        }

        if ($remainder > 0) {
            $parts[] = self::convertThreeDigits($remainder);
        }

        return implode(' و', $parts);
    }

    /**
     * تحويل عدد من 1 إلى 999 إلى كلمات عربية
     */
    private static function convertThreeDigits(int $num): string
    {
        $parts = [];

        $h = intdiv($num, 100);
        $rem = $num % 100;

        if ($h > 0) {
            $parts[] = self::$hundreds[$h];
        }

        if ($rem > 0) {
            if ($rem < 20) {
                $parts[] = self::$ones[$rem];
            } else {
                $t = intdiv($rem, 10);
                $o = $rem % 10;
                if ($o > 0) {
                    $parts[] = self::$ones[$o].' و'.self::$tens[$t];
                } else {
                    $parts[] = self::$tens[$t];
                }
            }
        }

        return implode(' و', $parts);
    }
}

