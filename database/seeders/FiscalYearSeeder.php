<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FiscalYear;

class FiscalYearSeeder extends Seeder
{
    public function run(): void
    {
        $years = [
            ['year' => 2025, 'is_current' => false],
            ['year' => 2026, 'is_current' => true],
        ];

        foreach ($years as $data) {
            FiscalYear::updateOrCreate(['year' => $data['year']], $data);
        }
    }
}
