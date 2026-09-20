<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

function testBuildCompleteAuditReport() {
    $spreadsheet = new Spreadsheet();
    
    // ─── Sheet 1: كافة البيانات المرفوعة ───
    $sheet1 = $spreadsheet->getActiveSheet();
    $sheet1->setRightToLeft(true);
    $sheet1->setTitle('كافة البيانات مع الملاحظات');
    $sheet1->setShowGridLines(true);

    $sheet1->mergeCells('A2:N2');
    $sheet1->setCellValue('A2', 'الشركة العامة لموانئ العراق - قسم الشؤون الرقابية والتجارية');
    $sheet1->getStyle('A2')->getFont()->setBold(true)->setSize(14)->getColor()->setARGB('FFFFFFFF');
    $sheet1->getStyle('A2')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF0F172A');
    $sheet1->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
    $sheet1->getRowDimension(2)->setRowHeight(28);

    $sheet1->mergeCells('A3:N3');
    $sheet1->setCellValue('A3', 'كشف التدقيق الرقابي وموقف الحاويات الكامل لشهر آب 2026 متضمناً تفاصيل الملاحظات الرقابية');
    $sheet1->getStyle('A3')->getFont()->setBold(true)->setSize(12)->getColor()->setARGB('FFFFFFFF');
    $sheet1->getStyle('A3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF1E293B');
    $sheet1->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
    $sheet1->getRowDimension(3)->setRowHeight(24);

    $headers = [
        'A' => 'ت',
        'B' => 'رقم الحاوية',
        'C' => 'الحجم',
        'D' => 'اسم الباخرة',
        'E' => 'نوع البضاعة',
        'F' => 'العائدية / المستلم',
        'G' => 'تاريخ الوصول',
        'H' => 'سنة الوصول',
        'I' => 'الرصيف / الساحة',
        'J' => 'الميناء',
        'K' => 'الجهة / الوزارة',
        'L' => 'الملاحظات المسجلة بالشيت',
        'M' => 'تفاصيل الملاحظة الرقابية',
        'N' => 'حالة التدقيق الرقابي',
    ];

    foreach ($headers as $col => $title) {
        $cell = $col . '5';
        $sheet1->setCellValue($cell, $title);
        $sheet1->getStyle($cell)->getFont()->setBold(true)->setSize(11)->getColor()->setARGB('FFFFFFFF');
        $sheet1->getStyle($cell)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF334155');
        $sheet1->getStyle($cell)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
    }
    $sheet1->getRowDimension(5)->setRowHeight(26);

    // Auto-fit
    foreach (range('A', 'N') as $col) {
        $sheet1->getColumnDimension($col)->setAutoSize(true);
    }

    $dir = storage_path('app/public/audit_reports');
    if (!is_dir($dir)) mkdir($dir, 0777, true);
    $path = $dir . '/test_complete_audit.xlsx';
    $writer = new Xlsx($spreadsheet);
    $writer->save($path);

    echo "Report saved to $path (Size: " . filesize($path) . " bytes)\n";
}

testBuildCompleteAuditReport();
