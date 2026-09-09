<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('container_status_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('port_id')->constrained('ports')->cascadeOnDelete();
            $table->foreignId('fiscal_year_id')->constrained('fiscal_years')->cascadeOnDelete();
            $table->foreignId('month_id')->constrained('months')->cascadeOnDelete();
            $table->date('report_date');              // تاريخ التقرير المُصدَر بالضبط
            $table->enum('container_type', ['abandoned', 'dangerous']); // متخلفة / خطرة
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            // كل ميناء يملك تقرير واحد لكل نوع في نفس الشهر والسنة
            $table->unique(['port_id', 'fiscal_year_id', 'month_id', 'container_type'], 'unique_port_year_month_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('container_status_records');
    }
};
