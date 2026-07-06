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
        Schema::create('seed_growings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('number', 100)->unique();
            $table->foreignUuid('farmer_id')->constrained()->restrictOnDelete();
            $table->foreignUuid('land_id')->constrained()->restrictOnDelete();
            $table->string('field_number', 100);
            $table->string('lot_number', 100)->nullable();
            $table->unsignedSmallInteger('season_year');
            $table->foreignUuid('rice_variety_id')->constrained()->restrictOnDelete();
            $table->foreignUuid('seed_class_id')->constrained()->restrictOnDelete();
            $table->foreignUuid('source_seed_item_id')->constrained('items')->restrictOnDelete();
            $table->foreignUuid('source_seed_warehouse_id')->constrained('warehouses')->restrictOnDelete();
            $table->decimal('source_seed_quantity', 12, 2);
            $table->decimal('field_area', 10, 2);
            $table->date('sowing_date')->nullable();
            $table->date('planting_date')->nullable();
            $table->date('preliminary_date')->nullable();
            $table->date('field_inspection_1_date')->nullable();
            $table->date('field_inspection_2_date')->nullable();
            $table->date('field_inspection_3_date')->nullable();
            $table->date('harvest_date')->nullable();
            $table->string('status', 50)->default('draft');
            $table->text('notes')->nullable();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['land_id', 'field_number', 'season_year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seed_growings');
    }
};
