<?php

namespace Database\Seeders;

use App\Models\Port;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class UserAndRoleSeeder extends Seeder
{
    public function run(): void
    {
        // إعادة تعيين كاش الصلاحيات
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. إنشاء وتحديث الأدوار بالأسماء العربية الواضحة
        $roles = [
            'المدير العام',
            'مسؤول المتابعة المركزية والعمليات',
            'مدخل بيانات الميناء',
            'مسؤول الإيراد المالي',
            'مدقق / مراجع',
        ];

        foreach ($roles as $roleName) {
            Role::updateOrCreate(
                ['name' => $roleName, 'guard_name' => 'web'],
                ['name' => $roleName, 'guard_name' => 'web']
            );
        }

        // حذف الأدوار الإنجليزية القديمة غير المستخدمة إن وُجدت
        $oldRoles = ['port_data_entry', 'revenue_officer', 'reviewer', 'operations_manager', 'finance_manager', 'general_manager'];
        foreach ($oldRoles as $oldRole) {
            Role::where('name', $oldRole)->delete();
        }

        // 2. مستخدم المدير العام
        $admin = User::updateOrCreate(
            ['email' => 'admin@gcpi.iq'],
            [
                'name'      => 'المدير العام',
                'email'     => 'admin@gcpi.iq',
                'password'  => Hash::make('password'),
                'port_id'   => null,
                'user_type' => 'general_manager',
            ]
        );
        $admin->syncRoles(['المدير العام']);

        // 3. مستخدم المتابعة المركزية والعمليات (لكافة البيانات والموانئ)
        $operations = User::updateOrCreate(
            ['email' => 'operations@gcpi.iq'],
            [
                'name'      => 'مسؤول المتابعة المركزية والعمليات',
                'email'     => 'operations@gcpi.iq',
                'password'  => Hash::make('password'),
                'port_id'   => null,
                'user_type' => 'operations_manager',
            ]
        );
        $operations->syncRoles(['مسؤول المتابعة المركزية والعمليات']);

        // 4. مستخدم المالية (خاص بإضافة ومتابعة الإيراد)
        $finance = User::updateOrCreate(
            ['email' => 'finance@gcpi.iq'],
            [
                'name'      => 'مسؤول الإيراد المالي',
                'email'     => 'finance@gcpi.iq',
                'password'  => Hash::make('password'),
                'port_id'   => null,
                'user_type' => 'finance_manager',
            ]
        );
        $finance->syncRoles(['مسؤول الإيراد المالي']);

        // 5. مستخدمو الموانئ الأربعة (مدخل بيانات مخصص لكل ميناء)
        $portUsers = [
            [
                'name'     => 'مدخل بيانات ميناء أم قصر الشمالي',
                'email'    => 'north.port@gcpi.iq',
                'port_id'  => 1,
            ],
            [
                'name'     => 'مدخل بيانات ميناء أم قصر الجنوبي',
                'email'    => 'south.port@gcpi.iq',
                'port_id'  => 2,
            ],
            [
                'name'     => 'مدخل بيانات ميناء خور الزبير',
                'email'    => 'khor.port@gcpi.iq',
                'port_id'  => 3,
            ],
            [
                'name'     => 'مدخل بيانات ميناء أبو فلوس',
                'email'    => 'flus.port@gcpi.iq',
                'port_id'  => 4,
            ],
        ];

        foreach ($portUsers as $pu) {
            $user = User::updateOrCreate(
                ['email' => $pu['email']],
                [
                    'name'      => $pu['name'],
                    'email'     => $pu['email'],
                    'password'  => Hash::make('password'),
                    'port_id'   => $pu['port_id'],
                    'user_type' => 'port_data_entry',
                ]
            );
            $user->syncRoles(['مدخل بيانات الميناء']);
        }
    }
}
