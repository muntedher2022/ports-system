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
        Schema::table('container_status_records', function (Blueprint $table) {
            $table->string('excel_file_path')->nullable()->after('notes');
            $table->string('excel_file_name')->nullable()->after('excel_file_path');
            $table->unsignedBigInteger('excel_file_size')->nullable()->after('excel_file_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('container_status_records', function (Blueprint $table) {
            $table->dropColumn(['excel_file_path', 'excel_file_name', 'excel_file_size']);
        });
    }
};
