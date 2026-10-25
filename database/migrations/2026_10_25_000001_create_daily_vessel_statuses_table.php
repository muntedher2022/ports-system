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
        Schema::create('daily_vessel_statuses', function (Blueprint $table) {
            $table->id();
            $table->date('record_date')->unique()->comment('تاريخ الموقف اليومي');
            
            // الربط بالسنة المالية والشهر للتقارير والمقارنات التراكمية
            $table->foreignId('fiscal_year_id')->nullable()->constrained('fiscal_years')->nullOnDelete();
            $table->foreignId('month_id')->nullable()->constrained('months')->nullOnDelete();

            // 1. منطقة انتظار الميناء النفطي والميناء النفطي
            $table->unsignedInteger('oil_port_waiting')->default(0)->comment('منطقة الانتظار');
            $table->unsignedInteger('oil_port_berth')->default(0)->comment('الميناء النفطي');

            // 2. منطقة الانتظار (مخطاف)
            $table->unsignedInteger('anchorage_waiting')->default(0)->comment('منطقة الانتظار (مخطاف)');

            // 3. عدد السفن الراسية في الموانئ والأرصفة
            $table->unsignedInteger('um_qasr_north')->default(0)->comment('أم قصر الشمالي (UMQ-N)');
            $table->unsignedInteger('um_qasr_south')->default(0)->comment('أم قصر الجنوبي (UMQ-S)');
            $table->unsignedInteger('khor_al_zubair')->default(0)->comment('خور الزبير (KZ)');
            $table->unsignedInteger('abu_flus')->default(0)->comment('أبو فلوس (ABF)');
            $table->unsignedInteger('lpg_berth')->default(0)->comment('رصيف الغاز (LPG)');

            // 4. الإجماليات المحسوبة تلقائياً
            $table->unsignedInteger('total_berthed')->default(0)->comment('إجمالي السفن الراسية');
            $table->unsignedInteger('total_overall')->default(0)->comment('الموقف الإجمالي (راسية + انتظار)');

            // 5. الملاحظات والتدقيق
            $table->text('notes')->nullable()->comment('ملاحظات الموقف التشغيلية');
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // فهارس للبحث والمقارنات التراكمية
            $table->index(['fiscal_year_id', 'month_id']);
            $table->index('record_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_vessel_statuses');
    }
};
