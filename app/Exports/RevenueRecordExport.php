<?php

namespace App\Exports;

use App\Models\FiscalYear;
use App\Models\Month;
use App\Models\RevenueCenter;
use App\Models\RevenueRecord;
use Illuminate\Support\Collection;
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

class RevenueRecordExport implements FromArray, WithTitle, WithStyles, WithColumnWidths, WithEvents, WithStrictNullComparison
{
    protected ?int $fiscalYearId;
    protected ?string $yearName;
    protected ?string $latestMonthName;

    public function __construct(?int $revenueCenterId = null, ?int $fiscalYearId = null)
    {
        $this->fiscalYearId = $fiscalYearId ?: (FiscalYear::where('is_current', true)->first()?->id ?? FiscalYear::orderBy('year', 'desc')->first()?->id);
        $yearObj = FiscalYear::find($this->fiscalYearId);
        $this->yearName = $yearObj ? (string) $yearObj->year : date('Y');
    }

    public function title(): string
    {
        return 'مصفوفة الإيراد الكلي والصافي';
    }

    public function array(): array
    {
        $allCenters = RevenueCenter::where('is_active', true)->orderBy('sort_order')->get();
        $allMonths  = Month::orderBy('month_number')->get();

        $records = RevenueRecord::where('fiscal_year_id', $this->fiscalYearId)->get();

        // تصفية المراكز التي تحتوي على إيرادات مسجلة فقط طوال السنة (استبعاد أي مركز ليس له بيانات طوال السنة)
        $centers = $allCenters->filter(function ($center) use ($records) {
            return $records->where('revenue_center_id', $center->id)->sum('gross_revenue') > 0
                || $records->where('revenue_center_id', $center->id)->sum('net_revenue') > 0;
        })->values();

        if ($centers->isEmpty()) {
            $centers = $allCenters;
        }

        $recordsGrouped = $records->groupBy(fn ($r) => $r->month_id . '_' . $r->revenue_center_id);

        // تصفية الأشهر لغاية آخر شهر تم إدخال بيانات له في السنة
        $maxMonthId = $records->filter(fn($r) => (float)$r->gross_revenue > 0 || (float)$r->net_revenue > 0)->max('month_id');
        $months = $maxMonthId 
            ? $allMonths->where('id', '<=', $maxMonthId)->values()
            : $allMonths;

        $centerTotals = array_fill_keys($centers->pluck('id')->toArray(), 0);
        $grandGrossTotal = 0;
        $grandNetTotal = 0;
        $latestMonth = $months->last()?->name_ar ?? 'تموز';

        $dataRows = [];

        // 1. صف رؤوس الأعمدة
        $headings = ['الشهر'];
        foreach ($centers as $center) {
            $headings[] = $center->name_ar;
        }
        $headings[] = 'الإيراد الكلي الشهري';
        $headings[] = 'الإيراد الصافي الشهري';

        // حساب البيانات للأشهر المسجلة فقط
        foreach ($months as $month) {
            $monthGross = 0;
            $monthNet = 0;
            $row = [$month->name_ar];

            foreach ($centers as $center) {
                $key = $month->id . '_' . $center->id;
                $record = $recordsGrouped->get($key)?->first();

                $gross = $record ? (float) $record->gross_revenue : 0;
                $net   = $record ? (float) $record->net_revenue : 0;

                if ($record && ($gross > 0 || $net > 0)) {
                    $latestMonth = $month->name_ar;
                }

                $row[] = $gross;
                $monthGross += $gross;
                $monthNet   += $net;
                $centerTotals[$center->id] += $gross;
            }

            $row[] = $monthGross;
            $row[] = $monthNet;

            $grandGrossTotal += $monthGross;
            $grandNetTotal   += $monthNet;

            $dataRows[] = $row;
        }

        $this->latestMonthName = $latestMonth;

        // صف المجموع الكلي
        $totalRow = ['المجموع'];
        foreach ($centers as $center) {
            $totalRow[] = $centerTotals[$center->id];
        }
        $totalRow[] = $grandGrossTotal;
        $totalRow[] = $grandNetTotal;

        $titleRow = ["الإيراد الكلي والصافي للشركة لغاية شهر {$latestMonth} {$this->yearName} بالدينار العراقي"];

        return array_merge([$titleRow, $headings], $dataRows, [$totalRow]);
    }

    public function columnWidths(): array
    {
        return [
            'A' => 16,
            'B' => 22,
            'C' => 22,
            'D' => 22,
            'E' => 20,
            'F' => 20,
            'G' => 24,
            'H' => 22,
            'I' => 26,
            'J' => 26,
            'K' => 26,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $lastRow = $sheet->getHighestRow();
        $lastColumn = $sheet->getHighestColumn();

        // 1. دمج وتنسيق صف العنوان الرئيسي (الصف 1)
        $sheet->mergeCells("A1:{$lastColumn}1");
        $sheet->getStyle("A1:{$lastColumn}1")->applyFromArray([
            'font' => [
                'bold'  => true,
                'color' => ['rgb' => '0F172A'],
                'size'  => 13,
                'name'  => 'Calibri',
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '7DD3FC'], // أزرق سماوي كما في الترويسة
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                    'color'       => ['rgb' => '0F172A'],
                ],
            ],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(38);

        // 2. تنسيق صف رؤوس الأعمدة (الصف 2)
        $sheet->getStyle("A2:{$lastColumn}2")->applyFromArray([
            'font' => [
                'bold'  => true,
                'color' => ['rgb' => '0F172A'],
                'size'  => 11,
                'name'  => 'Calibri',
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FACC15'], // أصفر مخضر/ليموني مطابق لجدول PDF
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
                'wrapText'   => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => ['rgb' => '000000'],
                ],
            ],
        ]);
        $sheet->getRowDimension(2)->setRowHeight(32);

        // 3. تنسيق خلايا البيانات الشهرية (الصفوف من 3 إلى 14)
        $dataEndRow = $lastRow - 1;
        $sheet->getStyle("A3:{$lastColumn}{$dataEndRow}")->applyFromArray([
            'font' => [
                'size' => 10,
                'name' => 'Calibri',
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => ['rgb' => '000000'],
                ],
            ],
        ]);

        for ($r = 3; $r <= $dataEndRow; $r++) {
            $sheet->getRowDimension($r)->setRowHeight(22);
        }

        // تنسيق الأرقام
        $sheet->getStyle("B3:{$lastColumn}{$lastRow}")->getNumberFormat()->setFormatCode('#,##0');

        // 4. تنسيق صف المجموع النهائي (الصف الأخير)
        $sheet->getStyle("A{$lastRow}:{$lastColumn}{$lastRow}")->applyFromArray([
            'font' => [
                'bold'  => true,
                'size'  => 11,
                'name'  => 'Calibri',
                'color' => ['rgb' => '0F172A'],
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'BAE6FD'], // أزرق سماوي هادئ لصف المجموع
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                    'color'       => ['rgb' => '000000'],
                ],
            ],
        ]);
        $sheet->getRowDimension($lastRow)->setRowHeight(28);

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
