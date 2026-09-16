<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('temp_container_status_details_backup')) {
            Schema::create('temp_container_status_details_backup', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('container_status_record_id');
                $table->unsignedBigInteger('container_entity_id');
                $table->string('year_label', 50);
                $table->integer('count')->default(0);
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });

            // Copy existing details to backup
            if (Schema::hasTable('container_status_details')) {
                $existing = DB::table('container_status_details')->get();
                foreach ($existing as $row) {
                    DB::table('temp_container_status_details_backup')->insert([
                        'container_status_record_id' => $row->container_status_record_id,
                        'container_entity_id'        => $row->container_entity_id,
                        'year_label'                 => $row->year_label,
                        'count'                      => $row->count,
                        'sort_order'                 => $row->sort_order,
                        'created_at'                 => $row->created_at,
                        'updated_at'                 => $row->updated_at,
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('temp_container_status_details_backup');
    }
};
