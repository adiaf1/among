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
        Schema::create('seed_production_inputs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('seed_production_id')->constrained('seed_productions')->cascadeOnDelete();
            $table->foreignUuid('stock_id')->constrained('stocks')->restrictOnDelete();
            $table->foreignUuid('item_id')->constrained('items')->restrictOnDelete();
            $table->foreignUuid('warehouse_id')->constrained('warehouses')->restrictOnDelete();
            $table->string('role', 50)->default('bahan_utama');
            $table->decimal('quantity', 12, 2);
            $table->string('unit', 50)->default('kg');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seed_production_inputs');
    }
};
