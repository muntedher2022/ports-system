<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('generated_reports', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['monthly_port', 'revenue_summary', 'yoy_comparison', 'company_executive']);
            $table->unsignedBigInteger('scope_id')->nullable()->comment('port_id أو revenue_center_id حسب نوع التقرير');
            $table->string('scope_type')->nullable()->comment('Port | RevenueCenter');
            $table->string('period')->comment('مثال: 2026-07 أو 2025-2026');
            $table->string('file_path')->nullable();
            $table->enum('status', ['pending', 'processing', 'done', 'failed'])->default('pending');
            $table->text('error_message')->nullable();
            $table->foreignId('generated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('generated_reports');
    }
};
