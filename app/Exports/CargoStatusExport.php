<?php

namespace App\Exports;

use App\Models\CargoEntity;
use App\Models\CargoStatusRecord;
use App\Models\FiscalYear;
use App\Models\Month;
use App\Models\Port;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class CargoStatusExport implements FromArray, WithTitle, WithStyles, WithColumnWidths, WithEvents, WithStrictNullComparison
{
    protected string $cargoType;
    protected ?int   $fiscalYearId;
    protected ?int   $monthId;
    protected ?int   $portId;

    public function __construct(
        string $cargoType    = 'abandoned',
        ?int   $fiscalYearId = null,
        ?int   $monthId      = null,
        ?int   $portId       = null
    ) {
        $this->cargoType    = $cargoType;
        $this->fiscalYearId = $fiscalYearId ?: (FiscalYear::where('is_current', true)->first()?->id ?? FiscalYear::orderBy('year', 'desc')->first()?->id);
        $this->monthId      = $monthId ?: (Month::where('month_number', now()->month)->first()?->id ?? Month::first()?->id);
        $this->portId       = $portId;
    }

    public function title(): string
    {
        return $this->cargoType === 'abandoned' ? 'المواد والبضائع المتخلفة' : 'المواد والبضائع الخطرة';
    }

    public function array(): array
    {
        $typeLabel  = $this->cargoType === 'abandoned' ? 'المتخلفة' : 'الخطرة';
        $monthObj   = Month::find($this->monthId);
        $yearObj    = FiscalYear::find($this->fiscalYearId);
        $monthName  = $monthObj ? "{$monthObj->month_number} - {$monthObj->name_ar}" : '';
        $yearName   = $yearObj ? (string) $yearObj->year : '';
        $portName   = $this->portId
            ? (Port::find($this->portId)?->name_ar ?? 'جميع الموانئ')
            : 'جميع الموانئ (مجمّع)';

        // Years to show: 2004 → current year
        $allYears = range(2004, (int) date('Y'));

        // Records
        $records = CargoStatusRecord::where('cargo_type', $this->cargoType)
            ->where('fiscal_year_id', $this->fiscalYearId)
            ->where('month_id', $this->monthId)
            ->when($this->portId, fn($q) => $q->where('port_id', $this->portId))
            ->with('details.entity')
            ->get();

        // Build data matrix: [entity_id][year] => count
        $dataMatrix  = [];
        $entityList  = [];
        $entityOrder = [];

        foreach ($records as $rec) {
            $sortedDetails = $rec->details->sortBy(fn($d) => [$d->sort_order ?: 999, $d->id]);
            foreach ($sortedDetails as $det) {
                $eid    = $det->cargo_entity_id;
                $year   = (int) $det->year_label;
                $entity = $det->entity;

                if (!$entity || $det->count <= 0) {
                    continue;
                }

                if (!isset($dataMatrix[$eid])) {
                    $dataMatrix[$eid]  = array_fill_keys($allYears, 0);
                    $entityList[$eid]  = $entity;
                    $entityOrder[$eid] = $det->sort_order ?: ($entity->sort_order ?: 999);
                }

                if (in_array($year, $allYears)) {
                    $dataMatrix[$eid][$year] += $det->count;
                }
            }
        }

        // Sort entities: government first, then private, respecting sort_order
        uksort($entityList, function($idA, $idB) use ($entityList, $entityOrder) {
            $entA  = $entityList[$idA];
            $entB  = $entityList[$idB];
            $typeA = $entA->entity_type ?? 'government';
            $typeB = $entB->entity_type ?? 'government';

            if ($typeA !== $typeB) {
                return $typeA === 'government' ? -1 : 1;
            }

            return ($entityOrder[$idA] ?? 999) <=> ($entityOrder[$idB] ?? 999);
        });

        // Remove years where all entities have 0 count
        $activeYears = array_filter($allYears, function($y) use ($dataMatrix) {
            foreach ($dataMatrix as $rows) {
                if (($rows[$y] ?? 0) > 0) return true;
            }
            return false;
        });
        if (empty($activeYears)) {
            $activeYears = [(int) date('Y') - 1, (int) date('Y')];
        }
        $activeYears = array_values($activeYears);

        // ---- Build rows ----
        $rows = [];

        // Row 1: Title
        $titleCols = array_merge(
            ["موقف المواد والبضائع {$typeLabel} في {$portName} لغاية شهر {$monthName} لسنة {$yearName}"],
            array_fill(0, count($activeYears), ''),
            ['']
        );
        $rows[] = $titleCols;

        // Row 2: Headers
        $headers = ['ت — عائدية المواد والبضائع'];
        foreach ($activeYears as $y) {
            $headers[] = "خلال عام {$y}";
        }
        $headers[] = 'المجموع';
        $rows[] = $headers;

        // Data rows
        $totalByYear   = array_fill_keys($activeYears, 0);
        $grandTotal    = 0;
        $govTotByYear  = array_fill_keys($activeYears, 0);
        $govGrand      = 0;
        $privTotByYear = array_fill_keys($activeYears, 0);
        $privGrand     = 0;

        $govRowNum  = 1;

        // Government rows
        foreach ($entityList as $eid => $ent) {
            if ($ent->entity_type !== 'government') continue;

            $row = ["{$govRowNum}. {$ent->name_ar}"];
            $rowTotal = 0;
            foreach ($activeYears as $y) {
                $val = (int) ($dataMatrix[$eid][$y] ?? 0);
                $row[] = $val;
                $totalByYear[$y]  += $val;
                $govTotByYear[$y] += $val;
                $rowTotal += $val;
                $grandTotal += $val;
                $govGrand   += $val;
            }
            if ($rowTotal <= 0) continue;
            $row[] = (int) $rowTotal;
            $rows[] = $row;
            $govRowNum++;
        }

        // Private rows
        foreach ($entityList as $eid => $ent) {
            if ($ent->entity_type !== 'private') continue;

            $row = ["القطاع الخاص"];
            $rowTotal = 0;
            foreach ($activeYears as $y) {
                $val = (int) ($dataMatrix[$eid][$y] ?? 0);
                $row[] = $val;
                $totalByYear[$y]   += $val;
                $privTotByYear[$y] += $val;
                $rowTotal += $val;
                $grandTotal += $val;
                $privGrand  += $val;
            }
            if ($rowTotal <= 0) continue;
            $row[] = (int) $rowTotal;
            $rows[] = $row;
        }

        // Grand total row
        $totalRow = ['المجموع'];
        foreach ($activeYears as $y) {
            $totalRow[] = (int) ($totalByYear[$y] ?? 0);
        }
        $totalRow[] = (int) $grandTotal;
        $rows[] = $totalRow;

        // Summary mini-table (below)
        $rows[] = [];
        $rows[] = ['', 'القطاع الحكومي',    $govGrand];
        $rows[] = ['', 'القطاع الخاص',      $privGrand];
        $rows[] = ['', 'الكلي',             $grandTotal];

        return $rows;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 34,  // Entity name
            'B' => 16,
            'C' => 16,
            'D' => 16,
            'E' => 16,
            'F' => 16,
            'G' => 16,
            'H' => 16,
            'I' => 16,
            'J' => 16,
            'K' => 16,
            'L' => 16,
            'M' => 16,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $lastRow    = $sheet->getHighestRow();
        $lastCol    = $sheet->getHighestColumn();

        // Row 1 - title
        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
            'font'      => ['bold' => true, 'size' => 13, 'name' => 'Calibri', 'color' => ['rgb' => '1c1917']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FDE68A']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => 'CA8A04']]],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(36);

        // Row 2 - headers
        $sheet->getStyle("A2:{$lastCol}2")->applyFromArray([
            'font'      => ['bold' => true, 'size' => 11, 'name' => 'Calibri', 'color' => ['rgb' => '1c1917']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FACC15']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D4A500']]],
        ]);
        $sheet->getRowDimension(2)->setRowHeight(28);

        // Data rows style
        for ($r = 3; $r <= $lastRow; $r++) {
            $cellA = (string) $sheet->getCell("A{$r}")->getValue();

            if ($cellA === 'المجموع') {
                // Grand total row
                $sheet->getStyle("A{$r}:{$lastCol}{$r}")->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0F172A']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '334155']]],
                ]);
                $sheet->getRowDimension($r)->setRowHeight(26);
            } elseif (in_array($cellA, ['', 'القطاع الحكومي', 'القطاع الخاص', 'الكلي'])) {
                // Mini summary block below
                if ($sheet->getCell("B{$r}")->getValue() !== null) {
                    $sheet->getStyle("B{$r}:C{$r}")->applyFromArray([
                        'font'      => ['bold' => true, 'size' => 10],
                        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EFF6FF']],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                        'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'BFDBFE']]],
                    ]);
                }
            } else {
                // Regular data row
                $isGov = !str_starts_with($cellA, 'القطاع الخاص');
                $color = $isGov ? 'BFDBFE' : 'BBF7D0';

                $sheet->getStyle("A{$r}")->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 10],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $color]],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']]],
                ]);

                $sheet->getStyle("B{$r}:{$lastCol}{$r}")->applyFromArray([
                    'font'      => ['size' => 10],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']]],
                ]);

                // Highlight total column for each row
                $sheet->getStyle("{$lastCol}{$r}")->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F1F5F9']],
                ]);

                $sheet->getRowDimension($r)->setRowHeight(22);
            }
        }

        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                // Set RTL orientation
                $event->sheet->getDelegate()->setRightToLeft(true);
            },
        ];
    }
}
