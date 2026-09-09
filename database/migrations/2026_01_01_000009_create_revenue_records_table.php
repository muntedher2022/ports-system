<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('revenue_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('revenue_center_id')->constrained('revenue_centers')->cascadeOnDelete();
            $table->foreignId('fiscal_year_id')->constrained('fiscal_years')->cascadeOnDelete();
            $table->foreignId('month_id')->constrained('months')->cascadeOnDelete();

            // الإيراد — يُدخل يدوياً لكل مركز (إجمالي وصافي)
            $table->decimal('gross_revenue', 20, 3)->default(0)->comment('الإيراد الكلي (يدخل يدوياً)');
            $table->decimal('net_revenue', 20, 3)->default(0)->comment('الإيراد الصافي (يدخل يدوياً)');

            // حالة سير العمل
            $table->enum('status', ['draft', 'submitted', 'approved', 'locked'])->default('draft');
            $table->text('reopen_reason')->nullable();

            // التدقيق
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // منع التكرار
            $table->unique(['revenue_center_id', 'fiscal_year_id', 'month_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('revenue_records');
    }
};
