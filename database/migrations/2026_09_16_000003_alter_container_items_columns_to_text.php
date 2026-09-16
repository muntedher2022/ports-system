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
        Schema::table('container_items', function (Blueprint $table) {
            $table->text('consignee')->nullable()->change();
            $table->text('ship_name')->nullable()->change();
            $table->string('berth', 255)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('container_items', function (Blueprint $table) {
            $table->string('consignee', 255)->nullable()->change();
            $table->string('ship_name', 255)->nullable()->change();
            $table->string('berth', 100)->nullable()->change();
        });
    }
};
