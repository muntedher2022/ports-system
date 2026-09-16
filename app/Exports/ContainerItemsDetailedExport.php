<?php

namespace App\Exports;

use App\Exports\Sheets\ContainerEntitySheetExport;
use App\Models\ContainerEntity;
use App\Models\ContainerItem;
use App\Models\FiscalYear;
use App\Models\Month;
use App\Models\Port;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ContainerItemsDetailedExport implements WithMultipleSheets
{
    protected string $containerType;
    protected ?int $fiscalYearId;
    protected ?int $monthId;
    protected ?int $portId;

    public function __construct(
        string $containerType = 'abandoned',
        ?int $fiscalYearId = null,
        ?int $monthId = null,
        ?int $portId = null
    ) {
        $this->containerType = $containerType;
        $this->fiscalYearId  = $fiscalYearId ?: (FiscalYear::where('is_current', true)->first()?->id ?? FiscalYear::orderBy('year', 'desc')->first()?->id);
        $this->monthId       = $monthId ?: (Month::where('month_number', now()->month)->first()?->id ?? Month::first()?->id);
        $this->portId        = $portId;
    }

    public function sheets(): array
    {
        $sheets = [];

        // Find entities that have active (in_port) container items matching the criteria
        $entityIds = ContainerItem::query()
            ->where('status', 'in_port')
            ->where('container_type', $this->containerType)
            ->when($this->fiscalYearId, fn($q) => $q->where('fiscal_year_id', $this->fiscalYearId))
            ->when($this->monthId, fn($q) => $q->where('month_id', $this->monthId))
            ->when($this->portId, fn($q) => $q->where('port_id', $this->portId))
            ->distinct()
            ->pluck('container_entity_id')
            ->all();

        $entities = ContainerEntity::whereIn('id', $entityIds)
            ->orderBy('entity_type', 'asc') // government first, then private
            ->orderBy('sort_order', 'asc')
            ->get();

        foreach ($entities as $entity) {
            $sheets[] = new ContainerEntitySheetExport(
                entity: $entity,
                containerType: $this->containerType,
                fiscalYearId: $this->fiscalYearId,
                monthId: $this->monthId,
                portId: $this->portId
            );
        }

        // If no sheets, add a fallback sheet with first entity or empty
        if (empty($sheets)) {
            $firstEntity = ContainerEntity::first() ?? new ContainerEntity(['name_ar' => 'لا توجد بيانات']);
            $sheets[] = new ContainerEntitySheetExport(
                entity: $firstEntity,
                containerType: $this->containerType,
                fiscalYearId: $this->fiscalYearId,
                monthId: $this->monthId,
                portId: $this->portId
            );
        }

        return $sheets;
    }
}
