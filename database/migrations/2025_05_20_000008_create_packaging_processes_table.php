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
        Schema::create('packaging_processes', function (Blueprint $table) {
            $table->id();
            $table->string('process_number')->unique();
            $table->date('process_date');
            
            // Foreign keys
            $table->foreignId('dry_rice_stock_id')->constrained('dry_rice_stocks');
            $table->foreignId('variety_id')->constrained('varieties');
            $table->foreignId('warehouse_id')->constrained('warehouses');
            $table->foreignId('packaging_unit_id')->constrained('units'); // satuan packing (misal: zak 50kg)
            
            // Data
            $table->decimal('input_weight', 10, 2); // berat yang diambil dari stok kering
            $table->integer('quantity_packed'); // jumlah kemasan yang dihasilkan
            $table->decimal('weight_per_package', 10, 2); // berat per kemasan
            $table->decimal('remaining_loose_weight', 10, 2)->default(0); // sisa berat yang tidak terpacking
            
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packaging_processes');
    }
};
