<?php

namespace App\Models;

use OwenIt\Auditing\Models\Audit as BaseAudit;

class Audit extends BaseAudit
{
    protected $table = 'audits';

    /**
     * اسم العملية بالعربية
     */
    public function getEventLabelAttribute(): string
    {
        return match ($this->event) {
            'created'        => 'إضافة قيد جديد',
            'updated'        => 'تعديل بيانات',
            'deleted'        => 'حذف مؤقت',
            'restored'       => 'استرداد من الحذف',
            'forceDeleted'   => 'حذف نهائي',
            'exported_excel' => 'تصدير ملف Excel',
            'exported_pdf'   => 'تصدير تقرير PDF',
            'printed'        => 'طباعة تقرير',
            'login'          => 'تسجيل دخول للنظام',
            'logout'         => 'تسجيل خروج',
            'approved'       => 'اعتماد رسمي للسجل',
            'submitted'      => 'تقديم السجل للاعتماد',
            default          => $this->event,
        };
    }

    /**
     * لون العملية في الواجهة
     */
    public function getEventColorAttribute(): string
    {
        return match ($this->event) {
            'created'        => 'success',
            'updated'        => 'info',
            'deleted'        => 'warning',
            'restored'       => 'primary',
            'forceDeleted'   => 'danger',
            'exported_excel' => 'success',
            'exported_pdf'   => 'danger',
            'printed'        => 'gray',
            'approved'       => 'success',
            'submitted'      => 'warning',
            default          => 'gray',
        };
    }

    /**
     * أيقونة العملية
     */
    public function getEventIconAttribute(): string
    {
        return match ($this->event) {
            'created'        => 'heroicon-o-plus-circle',
            'updated'        => 'heroicon-o-pencil-square',
            'deleted'        => 'heroicon-o-trash',
            'restored'       => 'heroicon-o-arrow-uturn-left',
            'forceDeleted'   => 'heroicon-o-x-circle',
            'exported_excel' => 'heroicon-o-table-cells',
            'exported_pdf'   => 'heroicon-o-document-arrow-down',
            'printed'        => 'heroicon-o-printer',
            'approved'       => 'heroicon-o-check-badge',
            'submitted'      => 'heroicon-o-paper-airplane',
            default          => 'heroicon-o-clock',
        };
    }

    /**
     * اسم الكيان/النموذج بالعربية
     */
    public function getAuditableTypeLabelAttribute(): string
    {
        $type = class_basename($this->auditable_type ?? '');

        return match ($type) {
            'MonthlyPortRecord'     => 'السجلات التشغيلية للموانئ',
            'RevenueRecord'         => 'سجلات الإيراد للموانئ والمراكز',
            'ContainerStatusRecord' => 'سجلات الحاويات المتخلفة والخطرة',
            'ContainerEntity'       => 'جهات ووزارات الحاويات',
            'ContainerStatusDetail' => 'تفاصيل أعداد الحاويات',
            'CargoStatusRecord'     => 'سجلات المواد والبضائع المتخلفة والخطرة',
            'CargoEntity'           => 'جهات ووزارات المواد والبضائع',
            'CargoStatusDetail'     => 'تفاصيل أعداد المواد والبضائع',
            'Port'                  => 'الموانئ',
            'RevenueCenter'         => 'مراكز الإيراد',
            'FiscalYear'            => 'السنوات المالية',
            'Month'                 => 'الأشهر',
            'User'                  => 'المستخدمون والحسابات',
            'Report'                => 'التقارير والمقارنات',
            default                 => $type ?: 'العمليات العامة',
        };
    }

    /**
     * وصف السجل المستهدف بشكل مقروء
     */
    public function getTargetRecordDescriptionAttribute(): string
    {
        if (!$this->auditable) {
            return $this->tags ?: ('سجل رقم #' . ($this->auditable_id ?? '—'));
        }

        $record = $this->auditable;

        if ($record instanceof MonthlyPortRecord) {
            return "{$record->port?->name_ar} - {$record->month?->name_ar} {$record->fiscalYear?->year}";
        }

        if ($record instanceof RevenueRecord) {
            return "{$record->revenueCenter?->name_ar} - {$record->month?->name_ar} {$record->fiscalYear?->year}";
        }

        if ($record instanceof ContainerStatusRecord) {
            $typeLabel = $record->container_type === 'dangerous' ? 'حاويات خطرة' : 'حاويات متخلفة';
            return "{$record->port?->name_ar} - [{$typeLabel}] شهر {$record->month?->name_ar} {$record->fiscalYear?->year}";
        }

        if ($record instanceof ContainerEntity) {
            return "جهة: {$record->name_ar} ({$record->entity_type_label})";
        }

        if ($record instanceof ContainerStatusDetail) {
            return "تفاصيل: {$record->entity?->name_ar} - سنة {$record->year_label} ({$record->count} حاوية)";
        }

        if ($record instanceof CargoStatusRecord) {
            $typeLabel = $record->cargo_type === 'dangerous' ? 'مواد خطرة' : 'مواد متخلفة';
            return "{$record->port?->name_ar} - [{$typeLabel}] شهر {$record->month?->name_ar} {$record->fiscalYear?->year}";
        }

        if ($record instanceof CargoEntity) {
            return "جهة مواد: {$record->name_ar} ({$record->entity_type_label})";
        }

        if ($record instanceof CargoStatusDetail) {
            return "تفاصيل مواد: {$record->entity?->name_ar} - سنة {$record->year_label} ({$record->count} مادة/طرد)";
        }

        if ($record instanceof Port) {
            return "ميناء: {$record->name_ar} ({$record->code})";
        }

        if ($record instanceof RevenueCenter) {
            return "مركز إيراد: {$record->name_ar}";
        }

        if ($record instanceof FiscalYear) {
            return "السنة المالية: {$record->year}";
        }

        if ($record instanceof User) {
            return "المستخدم: {$record->name} ({$record->email})";
        }

        return $this->tags ?: ('سجل رقم #' . ($this->auditable_id ?? '—'));
    }
}
