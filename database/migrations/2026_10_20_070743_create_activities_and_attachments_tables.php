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
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->string('title')->comment('اسم أو عنوان النشاط');
            $table->date('activity_date')->index()->comment('تاريخ النشاط');
            $table->time('activity_time')->nullable()->comment('وقت النشاط');
            $table->string('category')->default('اجتماع رسمي')->index()->comment('نوع النشاط');
            $table->string('location')->nullable()->comment('الموقع / الجهة المستهدفة');
            $table->enum('importance', ['routine', 'important', 'urgent'])->default('important')->index()->comment('درجة الأهمية');
            $table->enum('status', ['completed', 'in_progress', 'scheduled', 'postponed'])->default('completed')->index()->comment('حالة الإنجاز');
            $table->text('short_description')->comment('الوصف المختصر للنشاط');
            $table->longText('detailed_notes')->nullable()->comment('المخرجات والتوجيهات والملاحظات التفصيلية');
            $table->text('attendees')->nullable()->comment('أبرز الحاضرين أو الوفد المشارك');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete()->comment('المستخدم الذي أدرج النشاط');
            $table->boolean('is_confidential')->default(false)->index()->comment('نشاط سري / خاص');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('activity_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_id')->constrained('activities')->cascadeOnDelete();
            $table->string('file_path')->comment('مسار الملف المشفر أو المحمي');
            $table->string('file_name')->comment('الاسم الأصلي للملف');
            $table->string('file_type')->nullable()->comment('نوع الملف الميمي');
            $table->unsignedBigInteger('file_size')->default(0)->comment('حجم الملف بالبايت');
            $table->string('caption')->nullable()->comment('وصف أو تعليق للصورة / المرفق');
            $table->boolean('is_primary')->default(false)->comment('صورة بارزة للتقرير');
            $table->unsignedInteger('sort_order')->default(0)->comment('ترتيب الظهور');
            $table->timestamps();

            $table->index(['activity_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_attachments');
        Schema::dropIfExists('activities');
    }
};
