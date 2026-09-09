<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('port_id')->nullable()->after('password')->constrained('ports')->nullOnDelete()
                ->comment('الميناء المرتبط بالمستخدم (لمدخلي البيانات)');
            $table->string('user_type')->nullable()->after('port_id')
                ->comment('port_data_entry | revenue_officer | reviewer | operations_manager | finance_manager | general_manager');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['port_id']);
            $table->dropColumn(['port_id', 'user_type']);
        });
    }
};
