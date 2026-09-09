<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
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

        $admin->assignRole('general_manager');
    }
}
