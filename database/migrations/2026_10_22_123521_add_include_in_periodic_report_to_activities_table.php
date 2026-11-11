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
        Schema::table('activities', function (Blueprint $table) {
            $table->boolean('include_in_periodic_report')
                ->default(true)
                ->after('is_confidential')
                ->index()
                ->comment('تحديد ما إذا كان هذا النشاط يدرج في التقرير الدوري');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->dropColumn('include_in_periodic_report');
        });
    }
};
