<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ports', function (Blueprint $table) {
            $table->id();
            $table->string('name_ar');
            $table->string('code', 10)->unique(); // NORTH, SOUTH, KHZ, ABF
            $table->enum('type', ['container', 'general', 'oil', 'mixed'])->default('mixed');
            $table->boolean('has_containers')->default(true);
            $table->boolean('has_oil')->default(false);
            $table->boolean('has_cars')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ports');
    }
};
