<?php

namespace App\Exports;

use App\Models\FiscalYear;
use App\Models\Month;
use App\Models\RevenueCenter;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class RevenueRecordTemplateExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new RevenueDataSheet(),
            new RevenueInstructionsSheet(),
        ];
    }
}

class RevenueDataSheet implements FromArray, WithHeadings, WithTitle, WithStyles, WithColumnWidths, WithStrictNullComparison
{
    public function title(): string
    {
        return 'قالب بيانات الإيراد';
    }

    public function headings(): array
    {
        return [
            'revenue_center_id (رقم مركز الإيراد)',
            'fiscal_year_id (رقم السنة المالية)',
            'month_id (رقم الشهر)',
            'gross_revenue (الإيراد الكلي - دينار)',
            'net_revenue (الإيراد الصافي - دينار)',
        ];
    }

    public function array(): array
    {
        // صف مثال توضيحي
        return [
            [1, 1, 1, 37790127283, 28000000000],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getStyle('A1:E1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 12],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '065F46']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'wrapText' => true],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '6EE7B7']]],
        ]);

        $sheet->getStyle('A2:E2')->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D1FAE5']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '6EE7B7']]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $sheet->getRowDimension(1)->setRowHeight(45);

        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 28,
            'B' => 25,
            'C' => 20,
            'D' => 28,
            'E' => 28,
        ];
    }
}

class RevenueInstructionsSheet implements FromArray, WithTitle, WithStyles, WithColumnWidths
{
    public function title(): string
    {
        return 'تعليمات الرفع';
    }

    public function array(): array
    {
        $centers = RevenueCenter::orderBy('id')->get()->map(fn($c) => "  {$c->id} = {$c->name_ar}")->implode("\n");
        $years = FiscalYear::orderBy('id')->get()->map(fn($y) => "  {$y->id} = {$y->year}")->implode("\n");
        $months = Month::orderBy('id')->get()->map(fn($m) => "  {$m->id} = {$m->name_ar}")->implode("\n");

        return [
            ['تعليمات رفع قالب بيانات الإيراد للمراكز السبعة'],
            [''],
            ['قواعد الرفع:'],
            ['1. كل صف يمثل إيراد مركز واحد في شهر واحد'],
            ['2. يمكن رفع بيانات مراكز وأشهر متعددة في ملف واحد'],
            ['3. إذا كان السجل موجوداً مسبقاً سيتم تحديثه تلقائياً'],
            ['4. قيم الإيراد بالدينار العراقي الكامل (بدون فواصل أو أصفار عشرية)'],
            ['5. لا يجوز أن يتجاوز الإيراد الصافي الإيراد الكلي'],
            [''],
            ['مراكز الإيراد المتاحة:'],
            [$centers],
            [''],
            ['السنوات المالية:'],
            [$years],
            [''],
            ['الأشهر:'],
            [$months],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '065F46']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $sheet->getStyle('A3')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D1FAE5']],
        ]);

        foreach (['A10', 'A13', 'A16'] as $cell) {
            $sheet->getStyle($cell)->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '047857']],
            ]);
        }

        return [];
    }

    public function columnWidths(): array
    {
        return ['A' => 70];
    }
}
