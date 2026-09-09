<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('months', function (Blueprint $table) {
            $table->id();
            $table->string('name_ar', 30);
            $table->unsignedTinyInteger('month_number'); // 1-12
            $table->unique('month_number');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('months');
    }
};
