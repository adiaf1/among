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
        Schema::create('seed_productions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('number', 100)->unique();
            $table->date('production_date');
            $table->string('lot_number', 100)->nullable();
            $table->foreignUuid('rice_variety_id')->nullable()->constrained('rice_varieties')->restrictOnDelete();
            $table->foreignUuid('seed_class_id')->nullable()->constrained('seed_classes')->restrictOnDelete();
            $table->foreignUuid('output_warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $table->decimal('target_quantity', 12, 2)->nullable();
            $table->string('unit', 50)->default('kg');
            $table->string('status', 50)->default('proses');
            $table->text('notes')->nullable();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seed_productions');
    }
};
