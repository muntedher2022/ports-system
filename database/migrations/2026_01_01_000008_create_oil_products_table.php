<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('oil_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('monthly_port_record_id')->constrained('monthly_port_records')->cascadeOnDelete();
            $table->enum('direction', ['import', 'export'])->comment('مستورد / مصدَّر');
            $table->decimal('weight_tons', 18, 3)->default(0)->comment('الوزن بالطن');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('oil_products');
    }
};
