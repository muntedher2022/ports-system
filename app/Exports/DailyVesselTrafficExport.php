<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class DailyVesselTrafficExport implements WithColumnWidths, WithEvents, WithStrictNullComparison, WithTitle
{
    protected Collection $records;

    protected string $fromDate;

    protected string $toDate;

    protected ?string $notes;

    public function __construct(Collection $records, string $fromDate, string $toDate, ?string $notes = null)
    {
        $this->records = $records;
        $this->fromDate = $fromDate;
        $this->toDate = $toDate;
        $this->notes = $notes;
    }

    public function title(): string
    {
        return 'موقف حركة السفن';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 14, // التاريخ
            'B' => 13, // سفن الدخول
            'C' => 13, // سفن المغادرة
            'D' => 16, // منطقة الانتظار (مخطاف)
            'E' => 14, // الميناء النفطي
            'F' => 16, // منطقة الانتظار
            'G' => 15, // أم قصر الشمالي
            'H' => 15, // أم قصر الجنوبي
            'I' => 15, // خور الزبير
            'J' => 14, // أبو فلوس
            'K' => 14, // رصيف الغاز
            'L' => 15, // إجمالي الراسية
            'M' => 18, // الموقف الإجمالي
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // ضبط اتجاه الورقة من اليمين إلى اليسار (RTL)
                $sheet->setRightToLeft(true);

                // إظهار خطوط الشبكة
                $sheet->setShowGridLines(true);

                // ─── 1. ترويسة التقرير (العنوان والفترة) ───
                $sheet->mergeCells('A2:M2');
                $sheet->setCellValue('A2', 'إحصائية حركة السفن اليومية (في الانتظار والراسية وموقف السيطرة) في الموانئ العراقية');
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => ['name' => 'Calibri', 'size' => 14, 'bold' => true, 'color' => ['rgb' => '1B365D']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getRowDimension(2)->setRowHeight(30);

                $sheet->mergeCells('A3:M3');
                $sheet->setCellValue('A3', "للفترة من {$this->fromDate} لغاية {$this->toDate}");
                $sheet->getStyle('A3')->applyFromArray([
                    'font' => ['name' => 'Calibri', 'size' => 11, 'bold' => true, 'color' => ['rgb' => '4A5568']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getRowDimension(3)->setRowHeight(22);

                // ─── 2. عناوين الأعمدة المعتمدة ───
                $sheet->mergeCells('A5:A6');
                $sheet->setCellValue('A5', 'التاريخ');

                $sheet->mergeCells('B5:D5');
                $sheet->setCellValue('B5', 'السيطرة البحرية والانتظار');
                $sheet->setCellValue('B6', "سفن\nالدخول");
                $sheet->setCellValue('C6', "سفن\nالمغادرة");
                $sheet->setCellValue('D6', "منطقة الانتظار\n(مخطاف)");

                $sheet->mergeCells('E5:F5');
                $sheet->setCellValue('E5', 'الميناء النفطي');
                $sheet->setCellValue('E6', "الميناء\nالنفطي");
                $sheet->setCellValue('F6', "منطقة الانتظار");

                $sheet->mergeCells('G5:K5');
                $sheet->setCellValue('G5', 'السفن الراسية في الموانئ والأرصفة');

                $sheet->setCellValue('G6', "أم قصر الشمالي\n(UMQ-N)");
                $sheet->setCellValue('H6', "أم قصر الجنوبي\n(UMQ-S)");
                $sheet->setCellValue('I6', "خور الزبير\n(KZ)");
                $sheet->setCellValue('J6', "أبو فلوس\n(ABF)");
                $sheet->setCellValue('K6', "رصيف الغاز\n(LPG)");

                $sheet->mergeCells('L5:L6');
                $sheet->setCellValue('L5', "إجمالي السفن\nالراسية");

                $sheet->mergeCells('M5:M6');
                $sheet->setCellValue('M5', "الموقف الإجمالي\n(راسية + انتظار)");

                // تنسيق رؤوس الأعمدة الرئيسية (الصف 5)
                $sheet->getStyle('A5:M5')->applyFromArray([
                    'font' => ['name' => 'Calibri', 'size' => 11, 'bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1B365D']],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                ]);

                // تنسيق الرؤوس الفرعية (الصف 6)
                $sheet->getStyle('B6:K6')->applyFromArray([
                    'font' => ['name' => 'Calibri', 'size' => 10, 'bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '24497D']],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                ]);

                $sheet->getRowDimension(5)->setRowHeight(28);
                $sheet->getRowDimension(6)->setRowHeight(32);

                // ─── 3. كتابة البيانات اليومية ───
                $currentRow = 7;
                $startDataRow = 7;

                foreach ($this->records as $r) {
                    $dateStr = $r->record_date ? Carbon::parse($r->record_date)->format('d/m/Y') : '—';

                    $sheet->setCellValue("A{$currentRow}", $dateStr);
                    $sheet->setCellValue("B{$currentRow}", (int) $r->control_vessels_in);
                    $sheet->setCellValue("C{$currentRow}", (int) $r->control_vessels_out);
                    $sheet->setCellValue("D{$currentRow}", (int) $r->anchorage_waiting);
                    $sheet->setCellValue("E{$currentRow}", (int) $r->oil_port_berth);
                    $sheet->setCellValue("F{$currentRow}", (int) $r->oil_port_waiting);
                    $sheet->setCellValue("G{$currentRow}", (int) $r->um_qasr_north);
                    $sheet->setCellValue("H{$currentRow}", (int) $r->um_qasr_south);
                    $sheet->setCellValue("I{$currentRow}", (int) $r->khor_al_zubair);
                    $sheet->setCellValue("J{$currentRow}", (int) $r->abu_flus);
                    $sheet->setCellValue("K{$currentRow}", (int) $r->lpg_berth);

                    // صيغ الإجمالي لكل يوم مطابقة لملف الإكسل
                    $sheet->setCellValue("L{$currentRow}", "=SUM(G{$currentRow}:K{$currentRow})");
                    $sheet->setCellValue("M{$currentRow}", "=SUM(D{$currentRow},L{$currentRow})");

                    $sheet->getRowDimension($currentRow)->setRowHeight(20);
                    $currentRow++;
                }

                $endDataRow = max($currentRow - 1, $startDataRow);

                // تنسيق خلايا البيانات
                if ($endDataRow >= $startDataRow) {
                    $sheet->getStyle("A{$startDataRow}:M{$endDataRow}")->applyFromArray([
                        'font' => ['name' => 'Calibri', 'size' => 10],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical' => Alignment::VERTICAL_CENTER,
                        ],
                    ]);

                    // تمييز أعمدة السيطرة ومناطق الانتظار (مخطاف وانتظار نفطي)
                    $sheet->getStyle("B{$startDataRow}:B{$endDataRow}")->applyFromArray([
                        'font' => ['bold' => true, 'color' => ['rgb' => '1D4ED8']],
                    ]);
                    $sheet->getStyle("C{$startDataRow}:C{$endDataRow}")->applyFromArray([
                        'font' => ['bold' => true, 'color' => ['rgb' => 'B91C1C']],
                    ]);
                    $sheet->getStyle("D{$startDataRow}:D{$endDataRow}")->applyFromArray([
                        'font' => ['bold' => true, 'color' => ['rgb' => 'B45309']],
                    ]);
                    $sheet->getStyle("F{$startDataRow}:F{$endDataRow}")->applyFromArray([
                        'font' => ['bold' => true, 'color' => ['rgb' => 'B45309']],
                    ]);

                    // تمييز عمودي الإجماليات (L و M) بلون خفيف
                    $sheet->getStyle("L{$startDataRow}:L{$endDataRow}")->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F0F4F8']],
                        'font' => ['bold' => true],
                    ]);
                    $sheet->getStyle("M{$startDataRow}:M{$endDataRow}")->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E6F4EA']],
                        'font' => ['bold' => true, 'color' => ['rgb' => '137333']],
                    ]);
                }

                // ─── 4. سطر فترات المكوث الكلية (سفينة/يوم) ───
                $totalRow = $currentRow;
                $sheet->setCellValue("A{$totalRow}", 'مجموع فترات المكوث (سفينة/يوم)');
                $sheet->setCellValue("B{$totalRow}", "=SUM(B{$startDataRow}:B{$endDataRow})");
                $sheet->setCellValue("C{$totalRow}", "=SUM(C{$startDataRow}:C{$endDataRow})");
                $sheet->setCellValue("D{$totalRow}", "=SUM(D{$startDataRow}:D{$endDataRow})");
                $sheet->setCellValue("E{$totalRow}", "=SUM(E{$startDataRow}:E{$endDataRow})");
                $sheet->setCellValue("F{$totalRow}", "=SUM(F{$startDataRow}:F{$endDataRow})");
                $sheet->setCellValue("G{$totalRow}", "=SUM(G{$startDataRow}:G{$endDataRow})");
                $sheet->setCellValue("H{$totalRow}", "=SUM(H{$startDataRow}:H{$endDataRow})");
                $sheet->setCellValue("I{$totalRow}", "=SUM(I{$startDataRow}:I{$endDataRow})");
                $sheet->setCellValue("J{$totalRow}", "=SUM(J{$startDataRow}:J{$endDataRow})");
                $sheet->setCellValue("K{$totalRow}", "=SUM(K{$startDataRow}:K{$endDataRow})");
                $sheet->setCellValue("L{$totalRow}", "=SUM(L{$startDataRow}:L{$endDataRow})");
                $sheet->setCellValue("M{$totalRow}", "=SUM(M{$startDataRow}:M{$endDataRow})");

                $sheet->getStyle("A{$totalRow}:M{$totalRow}")->applyFromArray([
                    'font' => ['name' => 'Calibri', 'size' => 11, 'bold' => true, 'color' => ['rgb' => '0F2D59']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2E8F0']],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);
                $sheet->getRowDimension($totalRow)->setRowHeight(25);

                // تطبيق الحدود (Borders) على كامل الجدول من الصف 5 إلى سطر المجموع
                $sheet->getStyle("A5:M{$totalRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'CBD5E1'],
                        ],
                    ],
                ]);

                // ─── 5. سطر الملاحظات ───
                $notesRow = $totalRow + 2;
                $sheet->mergeCells("A{$notesRow}:M{$notesRow}");
                $defaultNotes = $this->notes ?: "ملاحظة/ الموقف إحصائي تراكمي لحركة السفن الراسية وفي الانتظار صادر عن الشركة العامة لموانئ العراق (GCPI).";
                $sheet->setCellValue("A{$notesRow}", $defaultNotes);
                $sheet->getStyle("A{$notesRow}")->applyFromArray([
                    'font' => ['name' => 'Calibri', 'size' => 10, 'italic' => true, 'color' => ['rgb' => '4A5568']],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_RIGHT,
                        'vertical' => Alignment::VERTICAL_TOP,
                        'wrapText' => true,
                    ],
                ]);
                $sheet->getRowDimension($notesRow)->setRowHeight(35);
            },
        ];
    }
}
