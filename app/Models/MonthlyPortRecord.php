<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class MonthlyPortRecord extends Model implements Auditable
{
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        // المعرّفات
        'port_id', 'fiscal_year_id', 'month_id',

        // 1. حركة البواخر والسيارات
        'total_container_ships',
        'general_cargo_ships',
        'oil_tankers_count',
        'car_carrier_ships',
        'imported_cars_count',
        'imported_cars_weight_tons',

        // 2. الحاويات المستوردة
        'imported_containers_weight_tons',
        'imported_containers_count',
        'imported_20ft',
        'imported_40ft',
        'imported_45ft',
        'imported_teu',

        // 3. الحاويات المصدرة
        'exported_empty_count',
        'exported_full_count',
        'exported_full_weight_tons',
        'exported_containers_count',
        'exported_20ft',
        'exported_40ft',
        'exported_45ft',
        'exported_teu',

        // 4. البضائع العامة والمتنوعة
        'general_cargo_weight_tons',

        // 5. النفط والمشتقات النفطية
        'oil_exported_tons',
        'oil_imported_tons',
        'oil_total_tons',

        // 6. الإيراد المالي
        'total_revenue',

        // سير العمل والتدقيق
        'status', 'reopen_reason',
        'created_by', 'submitted_by', 'approved_by',
        'submitted_at', 'approved_at',
    ];

    protected $casts = [
        'total_container_ships'           => 'integer',
        'general_cargo_ships'             => 'integer',
        'oil_tankers_count'               => 'integer',
        'car_carrier_ships'               => 'integer',
        'imported_cars_count'             => 'integer',
        'imported_containers_count'       => 'integer',
        'imported_20ft'                   => 'integer',
        'imported_40ft'                   => 'integer',
        'imported_45ft'                   => 'integer',
        'imported_teu'                    => 'integer',
        'exported_empty_count'            => 'integer',
        'exported_full_count'             => 'integer',
        'exported_containers_count'       => 'integer',
        'exported_20ft'                   => 'integer',
        'exported_40ft'                   => 'integer',
        'exported_45ft'                   => 'integer',
        'exported_teu'                    => 'integer',

        'imported_cars_weight_tons'       => 'decimal:3',
        'imported_containers_weight_tons' => 'decimal:3',
        'exported_full_weight_tons'       => 'decimal:3',
        'general_cargo_weight_tons'       => 'decimal:3',
        'oil_exported_tons'               => 'decimal:3',
        'oil_imported_tons'               => 'decimal:3',
        'oil_total_tons'                  => 'decimal:3',
        'total_revenue'                   => 'decimal:3',

        'submitted_at'                    => 'datetime',
        'approved_at'                     => 'datetime',
    ];

    protected $auditInclude = [
        'total_container_ships', 'general_cargo_ships', 'oil_tankers_count', 'car_carrier_ships',
        'imported_cars_count', 'imported_containers_weight_tons', 'imported_containers_count',
        'imported_20ft', 'imported_40ft', 'imported_45ft', 'imported_teu',
        'exported_empty_count', 'exported_full_count', 'exported_full_weight_tons',
        'exported_containers_count', 'exported_20ft', 'exported_40ft', 'exported_45ft', 'exported_teu',
        'general_cargo_weight_tons', 'oil_exported_tons', 'oil_imported_tons', 'oil_total_tons',
        'imported_cars_weight_tons', 'total_revenue', 'status',
    ];

    public function port(): BelongsTo
    {
        return $this->belongsTo(Port::class);
    }

    public function fiscalYear(): BelongsTo
    {
        return $this->belongsTo(FiscalYear::class);
    }

    public function month(): BelongsTo
    {
        return $this->belongsTo(Month::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // ===================== المؤشرات المشتقة والحسابات التلقائية =====================

    /**
     * إجمالي عدد البواخر لهذا الشهر
     */
    public function getTotalShipsAttribute(): int
    {
        return (int) $this->total_container_ships
            + (int) $this->general_cargo_ships
            + (int) $this->oil_tankers_count
            + (int) $this->car_carrier_ships;
    }

    /**
     * الطاقة الإنتاجية الكلية بالطن (حاويات مستوردة + حاويات مصدرة + بضائع عامة + نفط كلي + سيارات)
     */
    public function getTotalTonnageAttribute(): float
    {
        return (float) $this->imported_containers_weight_tons
            + (float) $this->exported_full_weight_tons
            + (float) $this->general_cargo_weight_tons
            + (float) $this->oil_total_tons
            + (float) $this->imported_cars_weight_tons;
    }

    /**
     * إجمالي الحاويات المكافئة TEU (مستورد + مصدر)
     */
    public function getTotalTeuAttribute(): int
    {
        return (int) $this->imported_teu + (int) $this->exported_teu;
    }

    /**
     * هل السجل مقفل للتعديل؟
     */
    public function isLocked(): bool
    {
        return in_array($this->status, ['approved', 'locked']);
    }
}
