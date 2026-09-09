<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // إعادة تعيين الكاش
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // الأدوار الستة كما في القسم 5 من الخطة
        $roles = [
            'port_data_entry'    => 'مدخل بيانات الميناء',
            'revenue_officer'    => 'محاسب الإيراد',
            'reviewer'           => 'مدقق / مراجع',
            'operations_manager' => 'مدير الطاقة الإنتاجية',
            'finance_manager'    => 'المدير المالي',
            'general_manager'    => 'المدير العام',
        ];

        foreach ($roles as $name => $label) {
            Role::updateOrCreate(
                ['name' => $name, 'guard_name' => 'web'],
                ['name' => $name, 'guard_name' => 'web']
            );
        }
    }
}
