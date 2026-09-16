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
        Schema::create('container_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('container_status_record_id')->nullable()->constrained('container_status_records')->nullOnDelete();
            $table->foreignId('port_id')->constrained('ports')->cascadeOnDelete();
            $table->foreignId('container_entity_id')->constrained('container_entities')->cascadeOnDelete();
            $table->foreignId('fiscal_year_id')->constrained('fiscal_years')->cascadeOnDelete();
            $table->foreignId('month_id')->constrained('months')->cascadeOnDelete();
            
            $table->enum('container_type', ['abandoned', 'dangerous'])->default('abandoned');
            $table->string('container_number', 50)->index();
            $table->string('size', 30)->nullable();
            $table->string('ship_name', 255)->nullable();
            $table->text('goods_type')->nullable();
            $table->string('consignee', 255)->nullable(); // عائدية الحاوية التفصيلية
            $table->date('arrival_date')->nullable();
            $table->string('arrival_year', 50)->default('2026')->index();
            $table->string('berth', 100)->nullable(); // الرصيف / الساحة
            
            // Lifecycle status
            $table->enum('status', ['in_port', 'discharged', 'transferred', 'under_procedure'])->default('in_port')->index();
            $table->foreignId('discharge_fiscal_year_id')->nullable()->constrained('fiscal_years')->nullOnDelete();
            $table->foreignId('discharge_month_id')->nullable()->constrained('months')->nullOnDelete();
            $table->date('discharge_date')->nullable();
            
            $table->boolean('is_manually_added')->default(false);
            $table->text('notes')->nullable();
            
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            
            $table->timestamps();
            $table->softDeletes();

            $table->index(['port_id', 'container_type', 'status'], 'idx_port_type_status');
            $table->index(['container_status_record_id', 'container_entity_id', 'arrival_year'], 'idx_rec_entity_year');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('container_items');
    }
};
