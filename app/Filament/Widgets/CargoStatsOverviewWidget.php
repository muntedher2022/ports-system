<?php

namespace App\Filament\Widgets;

use App\Models\CargoEntity;
use App\Models\CargoStatusDetail;
use App\Models\CargoStatusRecord;
use App\Models\FiscalYear;
use App\Models\Port;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class CargoStatsOverviewWidget extends Widget
{
    protected static ?string $heading = '📦 إحصائيات ومؤشرات المواد والبضائع المتخلفة والخطرة';
    protected static ?int $sort = 6;
    protected int | string | array $columnSpan = 'full';
    protected string $view = 'filament.widgets.cargo-stats-overview-widget';

    public function getHeading(): ?string
    {
        return static::$heading;
    }

    public static function canView(): bool
    {
        $user = Auth::user();
        if (! $user) return false;

        return $user->hasRole(['super_admin', 'المدير العام', 'general_manager', 'reviewer', 'مدقق / مراجع', 'operations_manager', 'مسؤول المتابعة المركزية والعمليات'])
            || $user->can('view_any_cargo::status::record')
            || ! ($user->isPortRestricted() && $user->port_id);
    }

    public function getStats(): array
    {
        $user   = Auth::user();
        $portId = ($user?->isPortRestricted() && $user?->port_id) ? $user->port_id : null;

        $currentYear = FiscalYear::where('is_current', true)->first()
            ?? FiscalYear::orderBy('year', 'desc')->first();

        // 1. المواد والبضائع المتخلفة
        $abandonedRecords = CargoStatusRecord::where('cargo_type', 'abandoned')
            ->when($currentYear, fn ($q) => $q->where('fiscal_year_id', $currentYear->id))
            ->when($portId, fn ($q) => $q->where('port_id', $portId))
            ->with(['details.entity'])
            ->get();

        $abandonedTotal   = 0;
        $abandonedGov     = 0;
        $abandonedPrivate = 0;

        foreach ($abandonedRecords as $rec) {
            foreach ($rec->details as $det) {
                $abandonedTotal += (int) $det->count;
                if ($det->entity?->entity_type === 'government') {
                    $abandonedGov += (int) $det->count;
                } else {
                    $abandonedPrivate += (int) $det->count;
                }
            }
        }

        // 2. المواد والبضائع الخطرة
        $dangerousRecords = CargoStatusRecord::where('cargo_type', 'dangerous')
            ->when($currentYear, fn ($q) => $q->where('fiscal_year_id', $currentYear->id))
            ->when($portId, fn ($q) => $q->where('port_id', $portId))
            ->with(['details.entity'])
            ->get();

        $dangerousTotal = 0;
        foreach ($dangerousRecords as $rec) {
            foreach ($rec->details as $det) {
                $dangerousTotal += (int) $det->count;
            }
        }

        // 3. الميناء الأكثر تسجيلاً للمواد
        $topPortName  = '—';
        $topPortCount = 0;
        $ports = Port::where('is_active', true)->get();
        foreach ($ports as $p) {
            $pCount = CargoStatusDetail::whereHas('record', function($q) use ($p, $currentYear) {
                $q->where('port_id', $p->id)
                  ->when($currentYear, fn($sq) => $sq->where('fiscal_year_id', $currentYear->id));
            })->sum('count');

            if ($pCount > $topPortCount) {
                $topPortCount = $pCount;
                $topPortName  = $p->name_ar;
            }
        }

        // 4. عدد الجهات المسجلة للبضائع
        $entitiesCount = CargoEntity::active()->count();

        return [
            'abandonedTotal'   => $abandonedTotal,
            'abandonedGov'     => $abandonedGov,
            'abandonedPrivate' => $abandonedPrivate,
            'dangerousTotal'   => $dangerousTotal,
            'topPortName'      => $topPortName,
            'topPortCount'     => $topPortCount,
            'entitiesCount'    => $entitiesCount,
            'currentYear'      => $currentYear?->year ?? date('Y'),
        ];
    }
}
