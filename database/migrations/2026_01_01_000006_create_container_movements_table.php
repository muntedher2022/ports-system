<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('container_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('monthly_port_record_id')->constrained('monthly_port_records')->cascadeOnDelete();
            $table->enum('direction', ['import', 'export'])->comment('استيراد / تصدير');
            $table->enum('full_or_empty', ['full', 'empty'])->nullable()->comment('مملوء/فارغ (للتصدير فقط)');

            // أحجام الحاويات
            $table->unsignedInteger('size_20ft')->default(0)->comment('حاويات 20 قدم');
            $table->unsignedInteger('size_40ft')->default(0)->comment('حاويات 40 قدم');
            $table->unsignedInteger('size_45ft')->default(0)->comment('حاويات 45 قدم');
            $table->unsignedInteger('teu_total')->default(0)->comment('الإجمالي بـ TEU');

            // الوزن
            $table->decimal('weight_tons', 18, 3)->default(0)->comment('الوزن بالطن');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('container_movements');
    }
};
