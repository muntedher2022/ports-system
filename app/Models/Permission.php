<?php

namespace App\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    /**
     * ترجمة الإجراء إلى اللغة العربية
     */
    public function getActionArabicAttribute(): string
    {
        $name = $this->name;

        if (str_contains($name, ':')) {
            [$action] = explode(':', $name, 2);
            return match ($action) {
                'ViewAny'        => 'عرض القائمة',
                'View'           => 'عرض التفاصيل',
                'Create'         => 'إضافة جديد',
                'Update'         => 'تعديل',
                'Delete'         => 'حذف',
                'DeleteAny'      => 'حذف متعدد',
                'Restore'        => 'استعادة',
                'RestoreAny'     => 'استعادة متعددة',
                'ForceDelete'    => 'حذف نهائي',
                'ForceDeleteAny' => 'حذف نهائي متعدد',
                'Replicate'      => 'نسخ وتكرار',
                'Reorder'        => 'إعادة ترتيب',
                default          => $action,
            };
        }

        if (str_starts_with($name, 'page_')) {
            return 'عرض الصفحة';
        }

        if (str_starts_with($name, 'widget_')) {
            return 'عرض الأداة الإحصائية';
        }

        return 'صلاحية مخصصة';
    }

    /**
     * اسم المورد أو الكيان التابع له باللغة العربية
     */
    public function getEntityArabicAttribute(): string
    {
        $name = $this->name;

        if (str_contains($name, ':')) {
            [, $entity] = explode(':', $name, 2);
            return match ($entity) {
                'MonthlyPortRecord'             => 'السجلات التشغيلية للموانئ',
                'RevenueRecord'                 => 'سجلات الإيراد',
                'RevenueCenter'                 => 'مراكز الإيراد',
                'ContainerStatusRecord'         => 'سجلات الحاويات المتخلفة والخطرة',
                'ContainerEntity'               => 'جهات الحاويات',
                'CargoStatusRecord'             => 'سجلات المواد والبضائع المتخلفة والخطرة',
                'CargoEntity'                   => 'جهات المواد والبضائع',
                'Port'                          => 'الموانئ',
                'FiscalYear'                    => 'السنوات المالية',
                'Month'                         => 'الأشهر',
                'User'                          => 'المستخدمون',
                'Role'                          => 'الأدوار والصلاحيات',
                'Audit'                         => 'سجل تتبع العمليات (Audit Trail)',
                'CapacityComparison'            => 'صفحة مقارنة الطاقات الإنتاجية',
                'MultiYearComparison'           => 'صفحة المقارنة السنوية المتعددة',
                'RevenueComparison'             => 'صفحة مقارنة الإيرادات',
                'StandardDeviationAnalytics'    => 'صفحة تحليل الانحراف المعياري',
                'TotalCumulativeCapacity'       => 'صفحة إجمالي الطاقات التراكمية',
                'TotalRevenueMatrix'            => 'مصفوفة إجمالي الإيرادات',
                'ContainerStatusMatrix'         => 'مصفوفة موقف الحاويات المتخلفة والخطرة',
                'CargoStatusMatrix'             => 'مصفوفة موقف المواد والبضائع',
                'PortStatsOverviewWidget'       => 'أداة الإحصائيات العامة للموانئ',
                'MonthlyPerformanceChart'       => 'رسم الأداء الشهري',
                'StandardDeviationOverviewWidget'=> 'أداة مؤشرات الانحراف المعياري',
                'PortShareChart'                => 'رسم الحصص النسبية للموانئ',
                'RevenueTrendsChart'            => 'رسم اتجاهات الإيراد',
                'LatestPortRecordsWidget'       => 'أداة أحدث السجلات التشغيلية',
                default                         => $entity,
            };
        }

        // صفحات مخصصة
        return match ($name) {
            'page_CapacityComparison'           => 'صفحة مقارنة الطاقات',
            'page_MultiYearComparison'          => 'صفحة المقارنة السنوية',
            'page_RevenueComparison'            => 'صفحة مقارنة الإيرادات',
            'page_StandardDeviationAnalytics'   => 'صفحة تحليل الانحراف المعياري',
            'page_TotalCumulativeCapacity'      => 'صفحة إجمالي الطاقات التراكمية',
            'page_TotalRevenueMatrix'           => 'مصفوفة إجمالي الإيرادات',
            'page_ContainerStatusMatrix'        => 'مصفوفة موقف الحاويات',
            'page_CargoStatusMatrix'            => 'مصفوفة موقف المواد والبضائع',
            'widget_PortStatsOverviewWidget'    => 'أداة إحصائيات الموانئ',
            'widget_MonthlyPerformanceChart'    => 'رسم الأداء الشهري',
            'widget_StandardDeviationOverviewWidget' => 'أداة الانحراف المعياري',
            'widget_PortShareChart'             => 'رسم حصص الموانئ',
            'widget_RevenueTrendsChart'         => 'رسم اتجاهات الإيرادات',
            'widget_LatestPortRecordsWidget'    => 'أداة أحدث السجلات',
            default                             => $name,
        };
    }

    /**
     * اسم المجموعة / النظام التابع له
     */
    public function getSystemGroupAttribute(): string
    {
        $name = $this->name;

        if (str_contains($name, 'MonthlyPortRecord') || str_contains($name, 'LatestPortRecords') || str_contains($name, 'MonthlyPerformance')) {
            return 'السجلات التشغيلية للموانئ';
        }

        if (str_contains($name, 'RevenueRecord') || str_contains($name, 'RevenueCenter') || str_contains($name, 'RevenueTrends') || str_contains($name, 'TotalRevenueMatrix') || str_contains($name, 'RevenueComparison')) {
            return 'نظام الإيرادات';
        }

        if (str_contains($name, 'Container')) {
            return 'نظام الحاويات المتخلفة والخطرة';
        }

        if (str_contains($name, 'Cargo')) {
            return 'نظام المواد والبضائع المتخلفة والخطرة';
        }

        if (str_contains($name, 'Port') || str_contains($name, 'FiscalYear') || str_contains($name, 'Month')) {
            return 'البيانات الرئيسية والإعدادات';
        }

        if (str_contains($name, 'User') || str_contains($name, 'Role') || str_contains($name, 'Audit')) {
            return 'إدارة النظام والأمان';
        }

        if (str_contains($name, 'Comparison') || str_contains($name, 'Analytics') || str_contains($name, 'Capacity') || str_contains($name, 'PortStats') || str_contains($name, 'PortShare') || str_contains($name, 'StandardDeviation')) {
            return 'التحليلات والمقارنات الإحصائية';
        }

        return 'أخرى';
    }

    /**
     * نوع الصلاحية (مورد / صفحة / أداة إحصائية)
     */
    public function getTypeLabelAttribute(): string
    {
        $name = $this->name;

        if (str_starts_with($name, 'page_') || str_contains($name, 'Comparison') || str_contains($name, 'Analytics') || str_contains($name, 'Matrix') || str_contains($name, 'Capacity')) {
            return 'صفحة / تقرير';
        }

        if (str_starts_with($name, 'widget_') || str_contains($name, 'Widget') || str_contains($name, 'Chart')) {
            return 'أداة لوحة التحكم';
        }

        return 'سجل بيانات (مورد)';
    }

    /**
     * لون الشارة بحسب النظام
     */
    public function getSystemGroupColorAttribute(): string
    {
        return match ($this->system_group) {
            'السجلات التشغيلية للموانئ'           => 'info',
            'نظام الإيرادات'                      => 'success',
            'نظام الحاويات المتخلفة والخطرة'       => 'warning',
            'نظام المواد والبضائع المتخلفة والخطرة' => 'danger',
            'البيانات الرئيسية والإعدادات'         => 'primary',
            'إدارة النظام والأمان'                 => 'danger',
            'التحليلات والمقارنات الإحصائية'       => 'purple',
            default                               => 'gray',
        };
    }
}
