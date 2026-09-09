<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Month;

class MonthSeeder extends Seeder
{
    public function run(): void
    {
        // الأسماء العربية مطابقة لورقة "الاشهر" في الملف الأصلي
        $months = [
            ['month_number' => 1,  'name_ar' => 'كانون الثاني'],
            ['month_number' => 2,  'name_ar' => 'شباط'],
            ['month_number' => 3,  'name_ar' => 'آذار'],
            ['month_number' => 4,  'name_ar' => 'نيسان'],
            ['month_number' => 5,  'name_ar' => 'أيار'],
            ['month_number' => 6,  'name_ar' => 'حزيران'],
            ['month_number' => 7,  'name_ar' => 'تموز'],
            ['month_number' => 8,  'name_ar' => 'آب'],
            ['month_number' => 9,  'name_ar' => 'أيلول'],
            ['month_number' => 10, 'name_ar' => 'تشرين الأول'],
            ['month_number' => 11, 'name_ar' => 'تشرين الثاني'],
            ['month_number' => 12, 'name_ar' => 'كانون الأول'],
        ];

        foreach ($months as $data) {
            Month::updateOrCreate(['month_number' => $data['month_number']], $data);
        }
    }
}
