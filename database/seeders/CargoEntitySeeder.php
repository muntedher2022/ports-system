<?php

namespace Database\Seeders;

use App\Models\CargoEntity;
use Illuminate\Database\Seeder;

class CargoEntitySeeder extends Seeder
{
    public function run(): void
    {
        $entities = [
            ['name_ar' => 'الأمم المتحدة', 'entity_type' => 'government', 'sort_order' => 1],
            ['name_ar' => 'وزارة الدفاع', 'entity_type' => 'government', 'sort_order' => 2],
            ['name_ar' => 'وزارة الخارجية', 'entity_type' => 'government', 'sort_order' => 3],
            ['name_ar' => 'وزارة النفط', 'entity_type' => 'government', 'sort_order' => 4],
            ['name_ar' => 'وزارة الكهرباء', 'entity_type' => 'government', 'sort_order' => 5],
            ['name_ar' => 'الاعمار والاسكان والبلديات', 'entity_type' => 'government', 'sort_order' => 6],
            ['name_ar' => 'وزارة التربية', 'entity_type' => 'government', 'sort_order' => 7],
            ['name_ar' => 'وزارة الشباب والرياضة', 'entity_type' => 'government', 'sort_order' => 8],
            ['name_ar' => 'محافظة البصرة', 'entity_type' => 'government', 'sort_order' => 9],
            ['name_ar' => 'وزارة العمل والشؤون الاجتماعية', 'entity_type' => 'government', 'sort_order' => 10],
            ['name_ar' => 'رئاسة مجلس الوزراء', 'entity_type' => 'government', 'sort_order' => 11],
            ['name_ar' => 'جامعة البصرة', 'entity_type' => 'government', 'sort_order' => 12],
            ['name_ar' => 'وزارة التخطيط', 'entity_type' => 'government', 'sort_order' => 13],
            ['name_ar' => 'وزارة الصحة', 'entity_type' => 'government', 'sort_order' => 14],
            ['name_ar' => 'منظمات انسانية', 'entity_type' => 'government', 'sort_order' => 15],
            ['name_ar' => 'محافظة بابل', 'entity_type' => 'government', 'sort_order' => 16],
            ['name_ar' => 'وزارة الاتصالات', 'entity_type' => 'government', 'sort_order' => 17],
            ['name_ar' => 'وزارة التعليم العالي والبحث العلمي', 'entity_type' => 'government', 'sort_order' => 18],
            ['name_ar' => 'وزارة البيشمركة', 'entity_type' => 'government', 'sort_order' => 19],
            ['name_ar' => 'وزارة الداخلية', 'entity_type' => 'government', 'sort_order' => 20],
            ['name_ar' => 'الاتحاد العراقي للبليارد', 'entity_type' => 'government', 'sort_order' => 21],
            ['name_ar' => 'القطاع الخاص', 'entity_type' => 'private', 'sort_order' => 100],
        ];

        foreach ($entities as $e) {
            CargoEntity::updateOrCreate(
                ['name_ar' => $e['name_ar']],
                [
                    'entity_type' => $e['entity_type'],
                    'sort_order'  => $e['sort_order'],
                    'is_active'   => true,
                ]
            );
        }
    }
}
