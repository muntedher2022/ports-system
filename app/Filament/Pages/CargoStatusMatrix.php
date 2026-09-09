<?php

namespace App\Filament\Pages;

use App\Enums\NavigationGroup;
use App\Models\CargoEntity;
use App\Models\CargoStatusDetail;
use App\Models\CargoStatusRecord;
use App\Models\FiscalYear;
use App\Models\Month;
use App\Models\Port;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;

class CargoStatusMatrix extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedTableCells;
    protected static ?string $navigationLabel = 'مصفوفة المواد الشاملة';
    protected static ?string $title = 'موقف المواد والبضائع المتخلفة والخطرة';
    protected static string|\UnitEnum|null $navigationGroup = NavigationGroup::Cargo;
    protected static ?int $navigationSort = 3;

    public static function canAccess(): bool
    {
        $user = Auth::user();
        if (! $user) return false;

        return true;
    }

    protected string $view = 'filament.pages.cargo-status-matrix';

    // Livewire properties
    public string $selectedCargoType = 'abandoned';
    public ?int $selectedPortId = null;
    public ?int $selectedFiscalYearId = null;
    public ?int $selectedMonthId = null;

    public function mount(): void
    {
        $this->selectedFiscalYearId = FiscalYear::where('is_current', true)->first()?->id
            ?? FiscalYear::orderBy('year', 'desc')->first()?->id;

        $this->selectedMonthId = Month::where('month_number', now()->month)->first()?->id
            ?? Month::first()?->id;
    }

    public function getPortsProperty(): \Illuminate\Database\Eloquent\Collection
    {
        return Port::where('is_active', true)
            ->where(fn($q) => $q->where('has_cargo_status', true)->orWhereHas('cargoStatusRecords'))
            ->orderBy('sort_order')
            ->get();
    }

    public function getFiscalYearsProperty(): \Illuminate\Database\Eloquent\Collection
    {
        return FiscalYear::orderBy('year', 'desc')->get();
    }

    public function getMonthsProperty(): \Illuminate\Database\Eloquent\Collection
    {
        return Month::orderBy('month_number')->get();
    }

    /** Build the matrix data */
    public function getMatrixDataProperty(): array
    {
        $years = range(2004, (int) date('Y'));

        // جلب السجلات المطابقة لفلاتر المستخدم
        $recordsQuery = CargoStatusRecord::where('cargo_type', $this->selectedCargoType)
            ->where('fiscal_year_id', $this->selectedFiscalYearId)
            ->where('month_id', $this->selectedMonthId)
            ->when($this->selectedPortId, fn($q) => $q->where('port_id', $this->selectedPortId));

        $records = $recordsQuery->with(['port', 'fiscalYear', 'month', 'details.entity'])->get();

        $matrix        = [];
        $totalByYear   = array_fill_keys($years, 0);
        $grandTotal    = 0;
        $govTotal      = 0;
        $privateTotal  = 0;

        foreach ($records as $record) {
            $sortedDetails = $record->details->sortBy(fn($d) => [$d->sort_order ?: 999, $d->id]);

            foreach ($sortedDetails as $detail) {
                $eid    = $detail->cargo_entity_id;
                $year   = (int) $detail->year_label;
                $entity = $detail->entity;

                if (!$entity || $detail->count <= 0) {
                    continue;
                }

                if (!isset($matrix[$eid])) {
                    $matrix[$eid] = [
                        'entity'     => $entity,
                        'years'      => array_fill_keys($years, 0),
                        'row_total'  => 0,
                        'sort_order' => $detail->sort_order ?: ($entity->sort_order ?: 999),
                    ];
                }

                if (in_array($year, $years)) {
                    $matrix[$eid]['years'][$year] += $detail->count;
                    $matrix[$eid]['row_total']    += $detail->count;
                    $totalByYear[$year]           += $detail->count;
                    $grandTotal                   += $detail->count;

                    if ($entity->entity_type === 'government') {
                        $govTotal += $detail->count;
                    } else {
                        $privateTotal += $detail->count;
                    }
                }
            }
        }

        // إبقاء فقط الجهات التي لديها رصيد مواد فعلي (row_total > 0)
        $activeEntities = array_filter($matrix, fn($row) => $row['row_total'] > 0);

        // ترتيب الجهات: القطاع الحكومي أولاً بالتسلسل المحدد، ثم القطاع الخاص
        uasort($activeEntities, function ($a, $b) {
            $typeA = $a['entity']->entity_type ?? 'government';
            $typeB = $b['entity']->entity_type ?? 'government';

            if ($typeA !== $typeB) {
                return $typeA === 'government' ? -1 : 1;
            }

            return ($a['sort_order'] ?? 999) <=> ($b['sort_order'] ?? 999);
        });

        // Remove years with no data across all entities (for display)
        $activeYears = array_filter($years, fn($y) => $totalByYear[$y] > 0);
        if (empty($activeYears)) {
            $activeYears = [(int) date('Y') - 1, (int) date('Y')];
        }
        $activeYears = array_values($activeYears);

        return [
            'entities'     => $activeEntities,
            'years'        => $activeYears,
            'totalByYear'  => $totalByYear,
            'grandTotal'   => $grandTotal,
            'govTotal'     => $govTotal,
            'privateTotal' => $privateTotal,
            'records'      => $records,
        ];
    }
}
