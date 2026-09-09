<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Port;

class PortSeeder extends Seeder
{
    public function run(): void
    {
        $ports = [
            [
                'name_ar'        => 'ميناء أم قصر الشمالي',
                'code'           => 'NORTH',
                'type'           => 'mixed',
                'has_containers' => true,
                'has_oil'        => false,
                'has_cars'       => true,
                'is_active'      => true,
                'sort_order'     => 1,
            ],
            [
                'name_ar'        => 'ميناء أم قصر الجنوبي',
                'code'           => 'SOUTH',
                'type'           => 'mixed',
                'has_containers' => true,
                'has_oil'        => true,
                'has_cars'       => false,
                'is_active'      => true,
                'sort_order'     => 2,
            ],
            [
                'name_ar'        => 'ميناء خور الزبير',
                'code'           => 'KHZ',
                'type'           => 'mixed',
                'has_containers' => false,
                'has_oil'        => true,
                'has_cars'       => false,
                'is_active'      => true,
                'sort_order'     => 3,
            ],
            [
                'name_ar'        => 'ميناء أبو فلوس',
                'code'           => 'ABF',
                'type'           => 'general',
                'has_containers' => true,
                'has_oil'        => false,
                'has_cars'       => false,
                'is_active'      => true,
                'sort_order'     => 4,
            ],
        ];

        foreach ($ports as $data) {
            Port::updateOrCreate(['code' => $data['code']], $data);
        }
    }
}
