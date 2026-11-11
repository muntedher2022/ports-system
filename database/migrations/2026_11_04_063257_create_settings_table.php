<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->text('value')->nullable();
            $table->string('group')->default('general');
            $table->timestamps();
        });

        // القيم الافتراضية
        DB::table('settings')->insert([
            [
                'key' => 'general_manager_name',
                'value' => 'عبد الله عاتي عبد الله',
                'group' => 'official_letters',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'general_manager_title',
                'value' => 'المدير العام / وكالة - رئيس مجلس الإدارة',
                'group' => 'official_letters',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'central_followup_officer_name',
                'value' => 'احمد حسين درويش',
                'group' => 'official_letters',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'central_followup_officer_title',
                'value' => 'م/ المتابعة المركزية والعمليات',
                'group' => 'official_letters',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
 