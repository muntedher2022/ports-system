<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ports', function (Blueprint $table) {
            if (!Schema::hasColumn('ports', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        Schema::table('revenue_centers', function (Blueprint $table) {
            if (!Schema::hasColumn('revenue_centers', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        Schema::table('fiscal_years', function (Blueprint $table) {
            if (!Schema::hasColumn('fiscal_years', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        Schema::table('months', function (Blueprint $table) {
            if (!Schema::hasColumn('months', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    public function down(): void
    {
        Schema::table('ports', fn (Blueprint $table) => $table->dropSoftDeletes());
        Schema::table('revenue_centers', fn (Blueprint $table) => $table->dropSoftDeletes());
        Schema::table('fiscal_years', fn (Blueprint $table) => $table->dropSoftDeletes());
        Schema::table('months', fn (Blueprint $table) => $table->dropSoftDeletes());
        Schema::table('users', fn (Blueprint $table) => $table->dropSoftDeletes());
    }
};
