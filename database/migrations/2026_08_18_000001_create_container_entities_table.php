<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('container_entities', function (Blueprint $table) {
            $table->id();
            $table->string('name_ar');                // اسم الجهة بالعربية
            $table->enum('entity_type', ['government', 'private'])->default('government'); // حكومي / خاص
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('container_entities');
    }
};
