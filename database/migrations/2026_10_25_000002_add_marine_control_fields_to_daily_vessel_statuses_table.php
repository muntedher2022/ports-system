<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('daily_vessel_statuses', function (Blueprint $table) {
            $table->unsignedInteger('control_vessels_in')->default(0)->after('lpg_berth')->comment('سفن الدخول - السيطرة البحرية');
            $table->unsignedInteger('control_vessels_out')->default(0)->after('control_vessels_in')->comment('سفن المغادرة - السيطرة البحرية');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_vessel_statuses', function (Blueprint $table) {
            $table->dropColumn([
                'control_vessels_in',
                'control_vessels_out',
            ]);
        });
    }
};
