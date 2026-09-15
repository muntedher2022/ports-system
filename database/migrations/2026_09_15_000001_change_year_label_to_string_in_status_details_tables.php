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
        Schema::table('cargo_status_details', function (Blueprint $table) {
            $table->string('year_label', 50)->change();
        });

        Schema::table('container_status_details', function (Blueprint $table) {
            $table->string('year_label', 50)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cargo_status_details', function (Blueprint $table) {
            $table->unsignedSmallInteger('year_label')->change();
        });

        Schema::table('container_status_details', function (Blueprint $table) {
            $table->integer('year_label')->change();
        });
    }
};
