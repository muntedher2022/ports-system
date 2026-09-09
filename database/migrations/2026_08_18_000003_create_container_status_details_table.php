<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('container_status_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('container_status_record_id')
                ->constrained('container_status_records')
                ->cascadeOnDelete();
            $table->foreignId('container_entity_id')
                ->constrained('container_entities')
                ->cascadeOnDelete();
            $table->integer('year_label');            // السنة الميلادية للبيانات (مثلاً 2004, 2025)
            $table->integer('count')->default(0);     // عدد الحاويات لهذه الجهة في هذه السنة
            $table->timestamps();

            $table->unique(['container_status_record_id', 'container_entity_id', 'year_label'], 'unique_record_entity_year');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('container_status_details');
    }
};
