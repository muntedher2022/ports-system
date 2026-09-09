<?php

namespace Database\Seeders;

use App\Models\ContainerEntity;
use Illuminate\Database\Seeder;

class ContainerEntitySeeder extends Seeder
{
    public function run(): void
    {
        // القطاع الحكومي — الجهات الموجودة في الشيت المرفق
        $governmentEntities = [
            ['name_ar' => 'الأمم المتحدة',               'sort_order' => 1],
            ['name_ar' => 'وزارة الدفاع',                'sort_order' => 2],
            ['name_ar' => 'وزارة الخارجية',              'sort_order' => 3],
            ['name_ar' => 'وزارة النفط',                 'sort_order' => 4],
            ['name_ar' => 'وزارة الكهرباء',              'sort_order' => 5],
            ['name_ar' => 'الإسكان والبلديات والأشغال', 'sort_order' => 6],
            ['name_ar' => 'وزارة التربية',               'sort_order' => 7],
            ['name_ar' => 'وزارة الشباب والرياضة',       'sort_order' => 8],
            ['name_ar' => 'محافظة البصرة',               'sort_order' => 9],
            ['name_ar' => 'وزارة العمل',                 'sort_order' => 10],
            ['name_ar' => 'رئاسة مجلس الوزراء',          'sort_order' => 11],
            ['name_ar' => 'جامعة البصرة',                'sort_order' => 12],
            ['name_ar' => 'وزارة التخطيط',               'sort_order' => 13],
            ['name_ar' => 'وزارة الصحة',                 'sort_order' => 14],
            ['name_ar' => 'منظمات المالية',              'sort_order' => 15],
            ['name_ar' => 'محافظة بابل',                 'sort_order' => 16],
        ];

        foreach ($governmentEntities as $entity) {
            ContainerEntity::firstOrCreate(
                ['name_ar' => $entity['name_ar'], 'entity_type' => 'government'],
                [
                    'sort_order' => $entity['sort_order'],
                    'is_active'  => true,
                ]
            );
        }

        // القطاع الخاص — جهة واحدة مجمّعة
        ContainerEntity::firstOrCreate(
            ['name_ar' => 'القطاع الخاص', 'entity_type' => 'private'],
            [
                'sort_order' => 100,
                'is_active'  => true,
            ]
        );
    }
}
