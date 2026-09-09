<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cargo_weights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('monthly_port_record_id')->constrained('monthly_port_records')->cascadeOnDelete();
            $table->enum('cargo_type', ['general', 'diverse'])->comment('بضائع عامة / متنوعة');
            $table->decimal('weight_tons', 18, 3)->default(0)->comment('الوزن بالطن');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cargo_weights');
    }
};
