<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RevenueCenter;
use App\Models\Port;

class RevenueCenterSeeder extends Seeder
{
    public function run(): void
    {
        // أولاً: مراكز إيراد مرتبطة بالموانئ الأربعة (لها طاقة إنتاجية)
        $operationalCenters = [
            ['name_ar' => 'ميناء أم قصر الشمالي', 'code' => 'RC_NORTH', 'port_code' => 'NORTH', 'is_operational' => true, 'sort_order' => 1],
            ['name_ar' => 'ميناء أم قصر الجنوبي', 'code' => 'RC_SOUTH', 'port_code' => 'SOUTH', 'is_operational' => true, 'sort_order' => 2],
            ['name_ar' => 'ميناء خور الزبير',     'code' => 'RC_KHZ',   'port_code' => 'KHZ',   'is_operational' => true, 'sort_order' => 3],
            ['name_ar' => 'ميناء أبو فلوس',       'code' => 'RC_ABF',   'port_code' => 'ABF',   'is_operational' => true, 'sort_order' => 4],
        ];

        foreach ($operationalCenters as $data) {
            $portId = Port::where('code', $data['port_code'])->value('id');
            RevenueCenter::updateOrCreate(
                ['code' => $data['code']],
                [
                    'name_ar'        => $data['name_ar'],
                    'port_id'        => $portId,
                    'is_operational' => $data['is_operational'],
                    'is_active'      => true,
                    'sort_order'     => $data['sort_order'],
                ]
            );
        }

        // ثانياً: مراكز إيراد إضافية (بلا طاقة إنتاجية تشغيلية)
        $nonOperationalCenters = [
            ['name_ar' => 'ميناء المعقل',    'code' => 'RC_MAAQIL',  'sort_order' => 5],
            ['name_ar' => 'ميناء البصرة النفطي', 'code' => 'RC_BOT', 'sort_order' => 6],
            ['name_ar' => 'مقر الشركة',      'code' => 'RC_HQ',      'sort_order' => 7],
        ];

        foreach ($nonOperationalCenters as $data) {
            RevenueCenter::updateOrCreate(
                ['code' => $data['code']],
                [
                    'name_ar'        => $data['name_ar'],
                    'port_id'        => null,
                    'is_operational' => false,
                    'is_active'      => true,
                    'sort_order'     => $data['sort_order'],
                ]
            );
        }
    }
}
