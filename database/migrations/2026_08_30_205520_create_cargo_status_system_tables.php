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
        // 1. جدول جهات ووزارات المواد والبضائع
        Schema::create('cargo_entities', function (Blueprint $table) {
            $table->id();
            $table->string('name_ar');
            $table->enum('entity_type', ['government', 'private'])->default('government');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. جدول سجلات موقف المواد الشهري
        Schema::create('cargo_status_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('port_id')->constrained('ports')->cascadeOnDelete();
            $table->foreignId('fiscal_year_id')->constrained('fiscal_years')->cascadeOnDelete();
            $table->foreignId('month_id')->constrained('months')->cascadeOnDelete();
            $table->date('report_date');
            $table->enum('cargo_type', ['abandoned', 'dangerous']); // متخلفة / خطرة
            $table->text('notes')->nullable();
            $table->string('excel_file_path')->nullable();
            $table->string('excel_file_name')->nullable();
            $table->unsignedBigInteger('excel_file_size')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            // كل ميناء يملك تقرير واحد لكل نوع في نفس الشهر والسنة
            $table->unique(['port_id', 'fiscal_year_id', 'month_id', 'cargo_type'], 'unique_cargo_port_year_month_type');
        });

        // 3. جدول تفاصيل وأعداد المواد حسب السنوات
        Schema::create('cargo_status_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cargo_status_record_id')->constrained('cargo_status_records')->cascadeOnDelete();
            $table->foreignId('cargo_entity_id')->constrained('cargo_entities')->cascadeOnDelete();
            $table->unsignedSmallInteger('year_label'); // سنة ورود المادة
            $table->unsignedInteger('count')->default(0); // العدد / الكمية
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            // يمنع تكرار نفس السنة لنفس الجهة في نفس التقرير
            $table->unique(['cargo_status_record_id', 'cargo_entity_id', 'year_label'], 'unique_cargo_record_entity_year');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cargo_status_details');
        Schema::dropIfExists('cargo_status_records');
        Schema::dropIfExists('cargo_entities');
    }
};
