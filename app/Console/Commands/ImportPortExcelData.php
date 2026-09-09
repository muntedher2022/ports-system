<?php

namespace App\Console\Commands;

use App\Models\FiscalYear;
use App\Models\Month;
use App\Models\MonthlyPortRecord;
use App\Models\Port;
use App\Models\RevenueCenter;
use App\Models\RevenueRecord;
use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\DB;

class ImportPortExcelData extends Command
{
    protected $signature = 'ports:import-excel {path2026?} {path2025?} {--fresh : احذف البيانات القديمة قبل الاستيراد}';
    protected $description = 'استيراد البيانات الحقيقية من ملفي الإكسل لعامي 2025 و 2026';

    private array $monthsMap = [
        'كانون الثاني' => 1,
        'شباط'         => 2,
        'شباط '        => 2,
        'اذار'         => 3,
        'أذار'         => 3,
        'نيسان'        => 4,
        'ايار'         => 5,
        'أيار'         => 5,
        'حزيران'       => 6,
        'تموز'         => 7,
        'اب'           => 8,
        'آب'           => 8,
        'ايلول'        => 9,
        'أيلول'        => 9,
        'تشرين الاول'  => 10,
        'تشرين الأول'  => 10,
        'تشرين الثاني' => 11,
        'كانون الاول'  => 12,
        'كانون الأول'  => 12,
    ];

    public function handle(): int
    {
        $path2026 = $this->argument('path2026') ?? 'C:\Users\MSI\OneDrive\Desktop\GCPI\معدل_الطاقة_للموانئ_لشهر_تموز_2026.xlsx';
        $path2025 = $this->argument('path2025') ?? 'C:\Users\MSI\OneDrive\Desktop\GCPI\data_2025.xlsx';

        if ($this->option('fresh')) {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            MonthlyPortRecord::truncate();
            RevenueRecord::truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            $this->info('🗑️ تم تفريغ الجداول القديمة بنجاح (--fresh)');
        }

        $year2025 = FiscalYear::firstOrCreate(['year' => 2025], ['is_current' => false]);
        $year2026 = FiscalYear::firstOrCreate(['year' => 2026], ['is_current' => true]);

        // 1. استيراد ملف 2025 (السنة الكاملة 12 شهر)
        if (file_exists($path2025)) {
            $this->info("📂 [1/2] استيراد بيانات سنة 2025 من: {$path2025}");
            $this->importYearFile($path2025, $year2025, 12);
        } else {
            $this->warn("⚠️ ملف 2025 غير موجود: {$path2025}");
        }

        // 2. استيراد ملف 2026 (7 أشهر لغاية تموز)
        if (file_exists($path2026)) {
            $this->info("📂 [2/2] استيراد بيانات سنة 2026 من: {$path2026}");
            $this->importYearFile($path2026, $year2026, 7);
        } else {
            $this->error("❌ ملف 2026 غير موجود: {$path2026}");
        }

        $this->newLine();
        $this->info('🎉 اكتمل استيراد بيانات 2025 و 2026 بالكامل بنجاح!');
        
        $this->table(
            ['المؤشر / الجدول', '2025', '2026', 'الإجمالي'],
            [
                [
                    'السجلات التشغيلية (الموانئ الأربعة)',
                    MonthlyPortRecord::where('fiscal_year_id', $year2025->id)->count(),
                    MonthlyPortRecord::where('fiscal_year_id', $year2026->id)->count(),
                    MonthlyPortRecord::count(),
                ],
                [
                    'سجلات الإيراد (المراكز السبعة)',
                    RevenueRecord::where('fiscal_year_id', $year2025->id)->count(),
                    RevenueRecord::where('fiscal_year_id', $year2026->id)->count(),
                    RevenueRecord::count(),
                ],
                [
                    'إجمالي الإيراد الكلي للشركة (د.ع)',
                    number_format(RevenueRecord::where('fiscal_year_id', $year2025->id)->sum('gross_revenue'), 0),
                    number_format(RevenueRecord::where('fiscal_year_id', $year2026->id)->sum('gross_revenue'), 0),
                    number_format(RevenueRecord::sum('gross_revenue'), 0),
                ]
            ]
        );

        return 0;
    }

    private function importYearFile(string $filePath, FiscalYear $fiscalYear, int $activeMonthsLimit): void
    {
        $spreadsheet = IOFactory::load($filePath);

        $portsSheets = [
            'الجنوبي'    => 'SOUTH',
            'الشمالي'    => 'NORTH',
            'ابو فلوس'   => 'ABF',
            'خور الزبير' => 'KHZ',
        ];

        // 1. الموانئ الأربعة
        foreach ($portsSheets as $sheetName => $portCode) {
            $port = Port::where('code', $portCode)->first();
            if (!$port) continue;

            $sheet = $spreadsheet->getSheetByName($sheetName);
            if (!$sheet) continue;

            $count = 0;
            for ($row = 4; $row <= 15; $row++) {
                $monthNameRaw = trim((string) $sheet->getCell("A{$row}")->getCalculatedValue());
                if (empty($monthNameRaw) || $monthNameRaw === 'المجموع') continue;

                $monthNum = $this->monthsMap[$monthNameRaw] ?? null;
                if (!$monthNum) continue;

                $month = Month::where('month_number', $monthNum)->first();
                if (!$month) continue;

                $f = fn ($col) => (float) ($sheet->getCell("{$col}{$row}")->getCalculatedValue() ?? 0);
                $fi = fn ($col) => (int) ($sheet->getCell("{$col}{$row}")->getCalculatedValue() ?? 0);

                $status = ($monthNum <= $activeMonthsLimit) ? 'approved' : 'draft';

                MonthlyPortRecord::updateOrCreate(
                    [
                        'port_id'        => $port->id,
                        'fiscal_year_id' => $fiscalYear->id,
                        'month_id'       => $month->id,
                    ],
                    [
                        'total_container_ships'           => $fi('B'),
                        'imported_containers_weight_tons' => $f('C'),
                        'imported_containers_count'       => $fi('D'),
                        'imported_20ft'                   => $fi('E'),
                        'imported_40ft'                   => $fi('F'),
                        'imported_45ft'                   => $fi('G'),
                        'imported_teu'                    => $fi('H'),
                        'exported_empty_count'            => $fi('I'),
                        'exported_full_count'             => $fi('J'),
                        'exported_full_weight_tons'       => $f('K'),
                        'exported_containers_count'       => $fi('L'),
                        'exported_20ft'                   => $fi('M'),
                        'exported_40ft'                   => $fi('N'),
                        'exported_45ft'                   => $fi('O'),
                        'exported_teu'                    => $fi('P'),
                        'general_cargo_ships'             => $fi('Q'),
                        'general_cargo_weight_tons'       => $f('R'),
                        'oil_tankers_count'               => $fi('S'),
                        'oil_exported_tons'               => $f('T'),
                        'oil_imported_tons'               => $f('U'),
                        'oil_total_tons'                  => $f('V'),
                        'imported_cars_count'             => $fi('W'),
                        'imported_cars_weight_tons'       => $f('X'),
                        'total_revenue'                   => $f('Y'),
                        'car_carrier_ships'               => 0,
                        'status'                          => $status,
                    ]
                );
                $count++;
            }
            $this->info("   ⚓ ميناء {$sheetName} ({$fiscalYear->year}): تم استيراد {$count} شهر");
        }

        // 2. الإيراد الكلي للمراكز السبعة
        $revenueSheet = $spreadsheet->getSheetByName('الايراد الكلي');
        if ($revenueSheet) {
            $centerCols = [
                'C' => 'RC_NORTH',
                'D' => 'RC_SOUTH',
                'E' => 'RC_KHZ',
                'F' => 'RC_ABF',
                'G' => 'RC_MAAQIL',
                'H' => 'RC_BOT',
                'I' => 'RC_HQ',
            ];

            $revCount = 0;
            for ($row = 4; $row <= 15; $row++) {
                $monthNameRaw = trim((string) $revenueSheet->getCell("B{$row}")->getCalculatedValue());
                if (empty($monthNameRaw) || $monthNameRaw === 'المجموع') continue;

                $monthNum = $this->monthsMap[$monthNameRaw] ?? null;
                if (!$monthNum) continue;

                $month = Month::where('month_number', $monthNum)->first();
                if (!$month) continue;

                $grossTotal = (float) $revenueSheet->getCell("J{$row}")->getCalculatedValue();
                $netTotal   = (float) $revenueSheet->getCell("K{$row}")->getCalculatedValue();
                $netRatio   = ($grossTotal > 0 && $netTotal > 0) ? ($netTotal / $grossTotal) : 0.52;

                foreach ($centerCols as $col => $centerCode) {
                    $center = RevenueCenter::where('code', $centerCode)->first();
                    if (!$center) continue;

                    $gross = (float) $revenueSheet->getCell("{$col}{$row}")->getCalculatedValue();
                    if ($gross <= 0) continue;

                    $net = round($gross * $netRatio, 3);

                    RevenueRecord::updateOrCreate(
                        [
                            'revenue_center_id' => $center->id,
                            'fiscal_year_id'    => $fiscalYear->id,
                            'month_id'          => $month->id,
                        ],
                        [
                            'gross_revenue' => $gross,
                            'net_revenue'   => $net,
                            'status'        => ($monthNum <= $activeMonthsLimit) ? 'approved' : 'draft',
                        ]
                    );
                    $revCount++;
                }
            }
            $this->info("   💰 إيرادات المراكز السبعة ({$fiscalYear->year}): تم استيراد {$revCount} سجل");
        }
    }
}
