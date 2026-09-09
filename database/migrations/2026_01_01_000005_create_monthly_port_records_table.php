<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monthly_port_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('port_id')->constrained('ports')->cascadeOnDelete();
            $table->foreignId('fiscal_year_id')->constrained('fiscal_years')->cascadeOnDelete();
            $table->foreignId('month_id')->constrained('months')->cascadeOnDelete();

            // حركة البواخر
            $table->unsignedInteger('total_container_ships')->default(0)->comment('عدد بواخر الحاويات الكلي');
            $table->unsignedInteger('general_cargo_ships')->default(0)->comment('عدد البواخر المتنوعة (بضائع عامة)');
            $table->unsignedInteger('oil_tankers_count')->default(0)->comment('عدد الناقلات النفطية الكلي');
            $table->unsignedInteger('car_carrier_ships')->default(0)->comment('عدد بواخر السيارات');
            $table->unsignedInteger('imported_cars_count')->default(0)->comment('عدد السيارات المستوردة');

            // حالة سير العمل
            $table->enum('status', ['draft', 'submitted', 'approved', 'locked'])->default('draft');
            $table->text('reopen_reason')->nullable()->comment('سبب إعادة الفتح إن وجد');

            // التدقيق
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // منع تكرار السجل لنفس الميناء/السنة/الشهر
            $table->unique(['port_id', 'fiscal_year_id', 'month_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monthly_port_records');
    }
};
