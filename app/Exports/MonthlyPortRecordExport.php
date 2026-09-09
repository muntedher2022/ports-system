<?php

namespace App\Exports;

use App\Models\MonthlyPortRecord;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
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

class MonthlyPortRecordExport implements FromCollection, WithHeadings, WithTitle, WithStyles, WithColumnWidths, WithEvents, WithStrictNullComparison
{
    protected ?int $portId;
    protected ?int $fiscalYearId;
    protected ?Collection $records;

    public function __construct(?int $portId = null, ?int $fiscalYearId = null, ?Collection $records = null)
    {
        $this->portId = $portId;
        $this->fiscalYearId = $fiscalYearId;
        $this->records = $records;
    }

    public function title(): string
    {
        return 'البيانات التشغيلية للموانئ';
    }

    public function headings(): array
    {
        return [
            'الميناء',
            'السنة',
            'الشهر',
            'عدد بواخر الحاويات الكلي',
            'الوزن بالطن (حاويات مستوردة)',
            'عدد الحاويات المستوردة الكلي',
            '20 قدم (مستورد)',
            '40 قدم (مستورد)',
            '45 قدم (مستورد)',
            'TEU المستورد',
            'عدد الحاويات المصدرة فارغة',
            'عدد الحاويات المصدرة مملوءة',
            'الوزن بالطن للمصدر المليان',
            'عدد الحاويات المصدرة الكلي',
            '20 قدم (مصدر)',
            '40 قدم (مصدر)',
            '45 قدم (مصدر)',
            'TEU المصدر',
            'عدد البواخر المتنوعة',
            'الوزن بالطن (بضائع متنوعة)',
            'عدد الناقلات النفطية الكلي',
            'نفط ومشتقاته مصدر بالطن',
            'نفط ومشتقاته مستورد بالطن',
            'نفط ومشتقاته الكلي بالطن',
            'عدد السيارات المستوردة',
            'الوزن بالطن (سيارات مستوردة)',
            'الإيراد الكلي للميناء (د.ع)',
            'الحالة',
        ];
    }

    public function collection(): Collection
    {
        $query = MonthlyPortRecord::with(['port', 'fiscalYear', 'month']);

        if ($this->records !== null && $this->records->isNotEmpty()) {
            return $this->formatRecords($this->records);
        }

        if ($this->portId) {
            $query->where('port_id', $this->portId);
        }

        if ($this->fiscalYearId) {
            $query->where('fiscal_year_id', $this->fiscalYearId);
        }

        $records = $query->orderBy('fiscal_year_id', 'desc')
            ->orderBy('month_id', 'asc')
            ->orderBy('port_id', 'asc')
            ->get();

        return $this->formatRecords($records);
    }

    protected function formatRecords(Collection $records): Collection
    {
        $rows = $records->map(function (MonthlyPortRecord $r) {
            $statusLabel = match ($r->status) {
                'approved'  => 'معتمد',
                'submitted' => 'مُقدَّم للاعتماد',
                'locked'    => 'مقفل',
                default     => 'مسودة',
            };

            $exportedTotal = ((int) ($r->exported_empty_count ?? 0)) + ((int) ($r->exported_full_count ?? 0));

            return [
                'port'                      => $r->port?->name_ar ?? '—',
                'fiscal_year'               => $r->fiscalYear?->year ?? '—',
                'month'                     => $r->month?->name_ar ?? "شهر {$r->month_id}",
                'total_container_ships'     => (int) ($r->total_container_ships ?? 0),
                'imported_weight_tons'      => (float) ($r->imported_containers_weight_tons ?? 0),
                'imported_count'            => (int) ($r->imported_containers_count ?? 0),
                'imported_20ft'             => (int) ($r->imported_20ft ?? 0),
                'imported_40ft'             => (int) ($r->imported_40ft ?? 0),
                'imported_45ft'             => (int) ($r->imported_45ft ?? 0),
                'imported_teu'              => (int) ($r->imported_teu ?? 0),
                'exported_empty'            => (int) ($r->exported_empty_count ?? 0),
                'exported_full'             => (int) ($r->exported_full_count ?? 0),
                'exported_full_weight_tons' => (float) ($r->exported_full_weight_tons ?? 0),
                'exported_total_count'      => $exportedTotal,
                'exported_20ft'             => (int) ($r->exported_20ft ?? 0),
                'exported_40ft'             => (int) ($r->exported_40ft ?? 0),
                'exported_45ft'             => (int) ($r->exported_45ft ?? 0),
                'exported_teu'              => (int) ($r->exported_teu ?? 0),
                'general_cargo_ships'       => (int) ($r->general_cargo_ships ?? 0),
                'general_cargo_weight_tons' => (float) ($r->general_cargo_weight_tons ?? 0),
                'oil_tankers_count'         => (int) ($r->oil_tankers_count ?? 0),
                'oil_exported_tons'         => (float) ($r->oil_exported_tons ?? 0),
                'oil_imported_tons'         => (float) ($r->oil_imported_tons ?? 0),
                'oil_total_tons'            => (float) ($r->oil_total_tons ?? 0),
                'imported_cars_count'       => (int) ($r->imported_cars_count ?? 0),
                'imported_cars_weight_tons' => (float) ($r->imported_cars_weight_tons ?? 0),
                'total_revenue'             => (float) ($r->total_revenue ?? 0),
                'status'                    => $statusLabel,
            ];
        });

        // إضافة صف المجموع في الأسفل إذا وُجدت سجلات
        if ($rows->isNotEmpty()) {
            $totalRow = [
                'port'                      => 'المجموع الكلي',
                'fiscal_year'               => '—',
                'month'                     => '—',
                'total_container_ships'     => (int) $rows->sum('total_container_ships'),
                'imported_weight_tons'      => (float) $rows->sum('imported_weight_tons'),
                'imported_count'            => (int) $rows->sum('imported_count'),
                'imported_20ft'             => (int) $rows->sum('imported_20ft'),
                'imported_40ft'             => (int) $rows->sum('imported_40ft'),
                'imported_45ft'             => (int) $rows->sum('imported_45ft'),
                'imported_teu'              => (int) $rows->sum('imported_teu'),
                'exported_empty'            => (int) $rows->sum('exported_empty'),
                'exported_full'             => (int) $rows->sum('exported_full'),
                'exported_full_weight_tons' => (float) $rows->sum('exported_full_weight_tons'),
                'exported_total_count'      => (int) $rows->sum('exported_total_count'),
                'exported_20ft'             => (int) $rows->sum('exported_20ft'),
                'exported_40ft'             => (int) $rows->sum('exported_40ft'),
                'exported_45ft'             => (int) $rows->sum('exported_45ft'),
                'exported_teu'              => (int) $rows->sum('exported_teu'),
                'general_cargo_ships'       => (int) $rows->sum('general_cargo_ships'),
                'general_cargo_weight_tons' => (float) $rows->sum('general_cargo_weight_tons'),
                'oil_tankers_count'         => (int) $rows->sum('oil_tankers_count'),
                'oil_exported_tons'         => (float) $rows->sum('oil_exported_tons'),
                'oil_imported_tons'         => (float) $rows->sum('oil_imported_tons'),
                'oil_total_tons'            => (float) $rows->sum('oil_total_tons'),
                'imported_cars_count'       => (int) $rows->sum('imported_cars_count'),
                'imported_cars_weight_tons' => (float) $rows->sum('imported_cars_weight_tons'),
                'total_revenue'             => (float) $rows->sum('total_revenue'),
                'status'                    => 'الإجمالي',
            ];

            $rows->push($totalRow);
        }

        return $rows;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 24, // الميناء
            'B' => 10, // السنة
            'C' => 14, // الشهر
            'D' => 20, // عدد بواخر الحاويات
            'E' => 24, // الوزن بالطن
            'F' => 22, // عدد الحاويات
            'G' => 14, // 20 قدم
            'H' => 14, // 40 قدم
            'I' => 14, // 45 قدم
            'J' => 16, // TEU
            'K' => 22, // المصدر فارغ
            'L' => 22, // المصدر مملوء
            'M' => 22, // الوزن بالطن مصدر
            'N' => 22, // عدد الحاويات المصدرة الكلي
            'O' => 14, // 20 قدم
            'P' => 14, // 40 قدم
            'Q' => 14, // 45 قدم
            'R' => 16, // TEU
            'S' => 18, // بواخر متنوعة
            'T' => 22, // وزن بضائع عامة
            'U' => 20, // ناقلات نفطية
            'V' => 20, // نفط مصدر
            'W' => 20, // نفط مستورد
            'X' => 20, // نفط كلي
            'Y' => 18, // سيارات
            'Z' => 22, // وزن سيارات
            'AA'=> 24, // الإيراد
            'AB'=> 14, // الحالة
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $lastRow = $sheet->getHighestRow();
        $lastColumn = 'AB';

        // تنسيق صف العناوين الرئيسي (Navy Blue)
        $sheet->getStyle("A1:{$lastColumn}1")->applyFromArray([
            'font' => [
                'bold'  => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size'  => 11,
                'name'  => 'Calibri',
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1E3A8A'], // أزرق كحلي ملكي
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
                'wrapText'   => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => ['rgb' => 'FFFFFF'],
                ],
            ],
        ]);

        $sheet->getRowDimension(1)->setRowHeight(35);

        // تنسيق خلايا البيانات
        if ($lastRow > 1) {
            $dataEndRow = $lastRow - 1; // صفوف البيانات قبل صف المجموع

            $sheet->getStyle("A2:{$lastColumn}{$dataEndRow}")->applyFromArray([
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
                        'color'       => ['rgb' => 'CBD5E1'],
                    ],
                ],
            ]);

            // تنسيق الأرقام لجميع الأعمدة العددية لضمان ظهور الصفر 0 دائماً
            $sheet->getStyle("D2:D{$lastRow}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("E2:E{$lastRow}")->getNumberFormat()->setFormatCode('#,##0.000');
            $sheet->getStyle("F2:L{$lastRow}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("M2:M{$lastRow}")->getNumberFormat()->setFormatCode('#,##0.000');
            $sheet->getStyle("N2:S{$lastRow}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("T2:T{$lastRow}")->getNumberFormat()->setFormatCode('#,##0.000');
            $sheet->getStyle("U2:U{$lastRow}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("V2:X{$lastRow}")->getNumberFormat()->setFormatCode('#,##0.000');
            $sheet->getStyle("Y2:Y{$lastRow}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("Z2:Z{$lastRow}")->getNumberFormat()->setFormatCode('#,##0.000');
            $sheet->getStyle("AA2:AA{$lastRow}")->getNumberFormat()->setFormatCode('#,##0');

            // تلوين عمود الإيراد الكلي بلون أخضر مميز
            $sheet->getStyle("AA2:AA{$dataEndRow}")->getFont()->setBold(true)->getColor()->setRGB('15803D');

            // ─── تنسيق صف المجموع الكلي (Last Row) ───
            $sheet->getStyle("A{$lastRow}:{$lastColumn}{$lastRow}")->applyFromArray([
                'font' => [
                    'bold'  => true,
                    'size'  => 11,
                    'name'  => 'Calibri',
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '0F172A'], // كحلي غامق فخم
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical'   => Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'top' => [
                        'borderStyle' => Border::BORDER_MEDIUM,
                        'color'       => ['rgb' => 'F59E0B'], // حافة ذهبية علوية
                    ],
                    'bottom' => [
                        'borderStyle' => Border::BORDER_DOUBLE,
                        'color'       => ['rgb' => 'FFFFFF'],
                    ],
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color'       => ['rgb' => '475569'],
                    ],
                ],
            ]);

            $sheet->getRowDimension($lastRow)->setRowHeight(30);
        }

        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                // ضبط اتجاه الصفحة من اليمين إلى اليسار (RTL)
                $event->sheet->setRightToLeft(true);
            },
        ];
    }
}
