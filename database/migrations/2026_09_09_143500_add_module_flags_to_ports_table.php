<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ports', function (Blueprint $table) {
            $table->boolean('has_monthly_records')->default(true)->after('type');
            $table->boolean('has_container_status')->default(true)->after('has_monthly_records');
            $table->boolean('has_cargo_status')->default(true)->after('has_container_status');
        });

        // ميناء المعقل افتراضياً لا يظهر في السجلات التشغيلية للطاقة الإنتاجية لكن يظهر في الحاويات والبضائع
        DB::table('ports')->where('code', 'MAQAL')->update([
            'has_monthly_records' => false,
            'has_container_status' => true,
            'has_cargo_status' => true,
        ]);
    }

    public function down(): void
    {
        Schema::table('ports', function (Blueprint $table) {
            $table->dropColumn([
                'has_monthly_records',
                'has_container_status',
                'has_cargo_status',
            ]);
        });
    }
};
