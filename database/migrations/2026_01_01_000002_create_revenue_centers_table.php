<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('revenue_centers', function (Blueprint $table) {
            $table->id();
            $table->string('name_ar');
            $table->string('code', 20)->unique();
            $table->foreignId('port_id')->nullable()->constrained('ports')->nullOnDelete();
            $table->boolean('is_operational')->default(true); // لديها طاقة إنتاجية تشغيلية
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('revenue_centers');
    }
};
