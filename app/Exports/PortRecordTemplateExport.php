<?php

namespace App\Exports;

use App\Models\FiscalYear;
use App\Models\Month;
use App\Models\Port;
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

class PortRecordTemplateExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new PortRecordDataSheet(),
            new PortRecordInstructionsSheet(),
        ];
    }
}

class PortRecordDataSheet implements FromArray, WithHeadings, WithTitle, WithStyles, WithColumnWidths, WithStrictNullComparison
{
    public function title(): string
    {
        return 'قالب البيانات التشغيلية للموانئ';
    }

    public function headings(): array
    {
        return [
            'port_id (رقم الميناء)',
            'fiscal_year_id (رقم السنة المالية)',
            'month_id (الشهر)',
            'total_container_ships (عدد بواخر الحاويات الكلي)',
            'imported_containers_weight_tons (الوزن بالطن - الحاويات المستوردة)',
            'imported_20ft (قدم 20 مستوردة)',
            'imported_40ft (قدم 40 مستوردة)',
            'imported_45ft (قدم 45 مستوردة)',
            'exported_empty_count (عدد الحاويات المصدرة فارغة)',
            'exported_full_count (عدد الحاويات المصدرة مملوءة)',
            'exported_full_weight_tons (الوزن بالطن للمصدر المليان)',
            'exported_20ft (قدم 20 مصدرة)',
            'exported_40ft (قدم 40 مصدرة)',
            'exported_45ft (قدم 45 مصدرة)',
            'general_cargo_ships (عدد البواخر المتنوعة)',
            'general_cargo_weight_tons (الوزن بالطن - بضائع عامة)',
            'oil_tankers_count (عدد الناقلات النفطية الكلي)',
            'oil_exported_tons (نفط ومشتقاته مصدر بالطن)',
            'oil_imported_tons (نفط ومشتقاته مستورد بالطن)',
            'imported_cars_count (عدد السيارات المستوردة)',
            'imported_cars_weight_tons (الوزن بالطن - سيارات)',
            'total_revenue (الإيراد الكلي - دينار)',
        ];
    }

    public function array(): array
    {
        // صف مثال توضيحي بنفس الترتيب المطلوب
        return [
            [
                1,              // port_id (أم قصر الشمالي)
                2,              // fiscal_year_id (2026)
                1,              // month_id (كانون الثاني)
                45,             // عدد بواخر الحاويات الكلي
                528414.000,     // الوزن بالطن للحاويات المستوردة
                10842,          // قدم 20 مستوردة
                24982,          // قدم 40 مستوردة
                0,              // قدم 45 مستوردة
                33418,          // عدد الحاويات المصدرة فارغة
                482,            // عدد الحاويات المصدرة مملوءة
                11420.000,      // الوزن بالطن للمصدر المليان
                11200,          // قدم 20 مصدرة
                22700,          // قدم 40 مصدرة
                0,              // قدم 45 مصدرة
                12,             // عدد البواخر المتنوعة
                184520.000,     // الوزن بالطن بضائع عامة
                0,              // عدد الناقلات النفطية
                0.000,          // نفط مصدر بالطن
                0.000,          // نفط مستورد بالطن
                0,              // عدد السيارات المستوردة
                0.000,          // الوزن بالطن للسيارات
                37790127283,    // الإيراد الكلي
            ],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        // رأس الجدول بالألوان المتناسقة
        $sheet->getStyle('A1:V1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => '000000'], 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FEF08A']], // أصفر كما في الصورة
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]],
        ]);

        // صف البيانات النموذجي
        $sheet->getStyle('A2:V2')->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']]],
        ]);

        $sheet->getRowDimension(1)->setRowHeight(55);
        $sheet->getRowDimension(2)->setRowHeight(25);

        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 20, 'B' => 22, 'C' => 16,
            'D' => 24, 'E' => 26,
            'F' => 18, 'G' => 18, 'H' => 18,
            'I' => 24, 'J' => 24, 'K' => 24,
            'L' => 18, 'M' => 18, 'N' => 18,
            'O' => 22, 'P' => 24, 'Q' => 22,
            'R' => 22, 'S' => 22, 'T' => 22,
            'U' => 22, 'V' => 25,
        ];
    }
}

class PortRecordInstructionsSheet implements FromArray, WithTitle, WithStyles, WithColumnWidths
{
    public function title(): string
    {
        return 'تعليمات الرفع';
    }

    public function array(): array
    {
        $ports = Port::orderBy('id')->get()->map(fn($p) => "  {$p->id} = {$p->name_ar}")->implode("\n");
        $years = FiscalYear::orderBy('id')->get()->map(fn($y) => "  {$y->id} = {$y->year}")->implode("\n");
        $months = Month::orderBy('id')->get()->map(fn($m) => "  {$m->id} = {$m->name_ar}")->implode("\n");

        return [
            ['تعليمات رفع قالب البيانات التشغيلية للموانئ'],
            [''],
            ['قواعد الرفع:'],
            ['1. الأعمدة مرتبة بنفس تسلسل جدول موقف الميناء الرسمي المعتمد.'],
            ['2. الحقول المحسوبة (مثل: TEUs، إجمالي الحاويات المصدرة، إجمالي أطنان النفط، إجمالي الطاقة الإنتاجية، إجمالي البواخر) يتم حسابها تلقائياً بالكامل من قِبل النظام ولا حاجة لإدخالها.'],
            ['3. الخانات غير المستخدمة لميناء معين (مثل النفط أو السيارات لميناء لا يتعامل بها) اتركها 0.'],
            ['4. قيم الأوزان بالطن تقبل حتى ثلاثة أرقام عشرية.'],
            ['5. قيم الإيراد بالدينار العراقي كأرقام فقط.'],
            [''],
            ['معرّفات الموانئ المتاحة (port_id):'],
            [$ports],
            [''],
            ['معرّفات السنوات المالية (fiscal_year_id):'],
            [$years],
            [''],
            ['معرّفات الأشهر (month_id):'],
            [$months],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A8A']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $sheet->getStyle('A3')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DBEAFE']],
        ]);

        return [];
    }

    public function columnWidths(): array
    {
        return ['A' => 80];
    }
}
