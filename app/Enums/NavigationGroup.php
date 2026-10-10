<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * UnitEnum (بدون value) لمجموعات التنقل في Filament v5
 * يجب أن يكون UnitEnum وليس BackedEnum
 */
enum NavigationGroup implements HasLabel
{
    case MasterData;
    case Operations;
    case Revenue;
    case Analytics;
    case Containers;
    case Cargo;
    case Reports;
    case SystemAdmin;

    public function getLabel(): string
    {
        return match ($this) {
            self::MasterData => 'البيانات الرئيسية',
            self::Operations => 'البيانات التشغيلية والإيراد',
            self::Revenue => 'الإيراد',
            self::Analytics => 'التحليلات والمقارنات',
            self::Containers => 'الحاويات المتخلفة والخطرة',
            self::Cargo => 'المواد والبضائع المتخلفة والخطرة',
            self::Reports => 'التقارير',
            self::SystemAdmin => 'إدارة النظام',
        };
    }
}
