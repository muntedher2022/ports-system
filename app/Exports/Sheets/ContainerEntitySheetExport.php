<?php

namespace App\Exports\Sheets;

use App\Models\ContainerEntity;
use App\Models\ContainerItem;
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

class ContainerEntitySheetExport implements FromArray, WithTitle, WithStyles, WithColumnWidths, WithEvents, WithStrictNullComparison
{
    protected ContainerEntity $entity;
    protected string $containerType;
    protected ?int $fiscalYearId;
    protected ?int $monthId;
    protected ?int $portId;
    protected array $items;

    public function __construct(
        ContainerEntity $entity,
        string $containerType = 'abandoned',
        ?int $fiscalYearId = null,
        ?int $monthId = null,
        ?int $portId = null
    ) {
        $this->entity = $entity;
        $this->containerType = $containerType;
        $this->fiscalYearId = $fiscalYearId;
        $this->monthId = $monthId;
        $this->portId = $portId;

        // Fetch container items for this entity in the port
        $query = ContainerItem::query()
            ->where('status', 'in_port') // Only active containers in port (exclude discharged)
            ->where('container_entity_id', $this->entity->id)
            ->where('container_type', $this->containerType)
            ->when($this->fiscalYearId, fn($q) => $q->where('fiscal_year_id', $this->fiscalYearId))
            ->when($this->monthId, fn($q) => $q->where('month_id', $this->monthId))
            ->when($this->portId, fn($q) => $q->where('port_id', $this->portId))
            ->with(['port', 'entity'])
            ->orderBy('port_id')
            ->orderBy('id', 'asc');

        $this->items = $query->get()->all();
    }

    public function title(): string
    {
        // Clean sheet name (Excel limits sheet names to 31 chars and bans *:\/?[] characters)
        $clean = preg_replace('/[*:\/?\[\]]/', '', $this->entity->name_ar);
        $clean = trim($clean);
        return mb_substr($clean, 0, 30) ?: 'الجهة';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,   // ت
            'B' => 20,  // رقم الحاوية
            'C' => 12,  // الحجم
            'D' => 22,  // اسم الباخرة
            'E' => 35,  // نوع البضاعة
            'F' => 35,  // عائدية الحاوية
            'G' => 16,  // تاريخ الوصول
            'H' => 16,  // الرصيف
            'I' => 22,  // الميناء
            'J' => 30,  // ملاحظات
        ];
    }

    public function array(): array
    {
        $rows = [];

        // 1. Title Banner
        $typeLabel = $this->containerType === 'dangerous' ? 'الخطرة' : 'المتخلفة';
        $monthObj = Month::find($this->monthId);
        $yearObj = FiscalYear::find($this->fiscalYearId);
        $monthStr = $monthObj ? "شهر {$monthObj->name_ar}" : '';
        $yearStr = $yearObj ? "لسنة {$yearObj->year}" : '';
        $portStr = $this->portId ? (Port::find($this->portId)?->name_ar ?? '') : 'كافة الموانئ';

        $title = "موقف الحاويات {$typeLabel} - {$this->entity->name_ar} ({$portStr}) {$monthStr} {$yearStr}";
        $rows[] = [$title, '', '', '', '', '', '', '', '', ''];

        // 2. Column Headers
        $rows[] = [
            'ت',
            'رقم الحاوية',
            'الحجم',
            'اسم الباخرة',
            'نوع البضاعة',
            'عائدية الحاوية',
            'تاريخ الوصول',
            'الرصيف',
            'الميناء',
            'ملاحظات',
        ];

        // 3. Data Rows
        $seq = 1;
        foreach ($this->items as $item) {
            $arrivalDateStr = $item->arrival_date
                ? $item->arrival_date->format('Y-m-d')
                : ($item->arrival_year ?: '2015');

            $rows[] = [
                $seq++,
                $item->container_number,
                $item->size ?: '',
                $item->ship_name ?: '',
                $item->goods_type ?: '',
                $item->consignee ?: '',
                $arrivalDateStr,
                $item->berth ?: '',
                $item->port?->name_ar ?: '',
                $item->notes ?: '',
            ];
        }

        // 4. Total Summary Row
        $rows[] = [
            'المجموع الكلي',
            count($this->items) . ' حاوية',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
        ];

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        $lastRow = count($this->items) + 3;

        // 1. Title row styling
        $sheet->mergeCells('A1:J1');
        $sheet->getStyle('A1:J1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 13, 'color' => ['rgb' => '1E293B']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2E8F0']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '94A3B8']]],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(32);

        // 2. Header row styling
        $headerColor = $this->containerType === 'dangerous' ? 'DC2626' : '1E40AF';
        $sheet->getStyle('A2:J2')->applyFromArray([
            'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $headerColor]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]],
        ]);
        $sheet->getRowDimension(2)->setRowHeight(28);

        // 3. Data rows styling
        for ($r = 3; $r < $lastRow; $r++) {
            $isEven = ($r % 2 === 0);
            $bg = $isEven ? 'F8FAFC' : 'FFFFFF';
            $sheet->getStyle("A{$r}:J{$r}")->applyFromArray([
                'font' => ['size' => 10, 'name' => 'Calibri'],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bg]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']]],
            ]);
            $sheet->getStyle("B{$r}")->getFont()->setBold(true);
            $sheet->getRowDimension($r)->setRowHeight(22);
        }

        // 4. Grand total row styling
        $sheet->getStyle("A{$lastRow}:J{$lastRow}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => '0F172A']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FEF08A']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => 'CA8A04']]],
        ]);
        $sheet->getRowDimension($lastRow)->setRowHeight(26);

        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->setRightToLeft(true);
            },
        ];
    }
}
