<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('monthly_port_records', function (Blueprint $table) {
            // الحاويات المستوردة
            $table->decimal('imported_containers_weight_tons', 15, 3)->default(0)->after('imported_cars_count')->comment('الوزن بالطن للحاويات المستوردة');
            $table->unsignedInteger('imported_containers_count')->default(0)->after('imported_containers_weight_tons')->comment('عدد الحاويات المستوردة');
            $table->unsignedInteger('imported_20ft')->default(0)->after('imported_containers_count')->comment('حاويات مستوردة 20 قدم');
            $table->unsignedInteger('imported_40ft')->default(0)->after('imported_20ft')->comment('حاويات مستوردة 40 قدم');
            $table->unsignedInteger('imported_45ft')->default(0)->after('imported_40ft')->comment('حاويات مستوردة 45 قدم');
            $table->unsignedInteger('imported_teu')->default(0)->after('imported_45ft')->comment('TEU المستورد');

            // الحاويات المصدرة
            $table->unsignedInteger('exported_empty_count')->default(0)->after('imported_teu')->comment('عدد الحاويات المصدرة فارغة');
            $table->unsignedInteger('exported_full_count')->default(0)->after('exported_empty_count')->comment('عدد الحاويات المصدرة مملوءة');
            $table->decimal('exported_full_weight_tons', 15, 3)->default(0)->after('exported_full_count')->comment('الوزن بالطن للحاويات المصدرة المليانة');
            $table->unsignedInteger('exported_containers_count')->default(0)->after('exported_full_weight_tons')->comment('عدد الحاويات المصدرة الكلي');
            $table->unsignedInteger('exported_20ft')->default(0)->after('exported_containers_count')->comment('حاويات مصدرة 20 قدم');
            $table->unsignedInteger('exported_40ft')->default(0)->after('exported_20ft')->comment('حاويات مصدرة 40 قدم');
            $table->unsignedInteger('exported_45ft')->default(0)->after('exported_40ft')->comment('حاويات مصدرة 45 قدم');
            $table->unsignedInteger('exported_teu')->default(0)->after('exported_45ft')->comment('TEU المصدر');

            // البضائع المتنوعة والعامة
            $table->decimal('general_cargo_weight_tons', 15, 3)->default(0)->after('exported_teu')->comment('الوزن بالطن للبضائع المتنوعة');

            // المشتقات النفطية
            $table->decimal('oil_exported_tons', 15, 3)->default(0)->after('general_cargo_weight_tons')->comment('نفط ومشتقات مصدر بالطن');
            $table->decimal('oil_imported_tons', 15, 3)->default(0)->after('oil_exported_tons')->comment('نفط ومشتقات مستورد بالطن');
            $table->decimal('oil_total_tons', 15, 3)->default(0)->after('oil_imported_tons')->comment('نفط ومشتقاته الكلي بالطن');

            // وزن السيارات المستوردة
            $table->decimal('imported_cars_weight_tons', 15, 3)->default(0)->after('oil_total_tons')->comment('وزن السيارات المستوردة بالطن');

            // إيراد الميناء للشهر
            $table->decimal('total_revenue', 18, 3)->default(0)->after('imported_cars_weight_tons')->comment('الإيراد الكلي للميناء للشهر');
        });
    }

    public function down(): void
    {
        Schema::table('monthly_port_records', function (Blueprint $table) {
            $table->dropColumn([
                'imported_containers_weight_tons',
                'imported_containers_count',
                'imported_20ft',
                'imported_40ft',
                'imported_45ft',
                'imported_teu',
                'exported_empty_count',
                'exported_full_count',
                'exported_full_weight_tons',
                'exported_containers_count',
                'exported_20ft',
                'exported_40ft',
                'exported_45ft',
                'exported_teu',
                'general_cargo_weight_tons',
                'oil_exported_tons',
                'oil_imported_tons',
                'oil_total_tons',
                'imported_cars_weight_tons',
                'total_revenue',
            ]);
        });
    }
};
