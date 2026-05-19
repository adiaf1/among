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
        Schema::create('packed_stocks', function (Blueprint $table) {
            $table->id();
            $table->string('stock_number')->unique();
            $table->date('stock_date');
            
            // Foreign keys
            $table->foreignId('packaging_process_id')->constrained('packaging_processes');
            $table->foreignId('variety_id')->constrained('varieties');
            $table->foreignId('warehouse_id')->constrained('warehouses');
            $table->foreignId('unit_id')->constrained('units'); // satuan kemasan
            
            // Data
            $table->integer('quantity'); // jumlah kemasan
            $table->decimal('weight_per_package', 10, 2); // berat per kemasan
            $table->decimal('total_weight', 10, 2); // total berat
            $table->integer('remaining_quantity'); // sisa kemasan yang belum terjual
            
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packed_stocks');
    }
};
