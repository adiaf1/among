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
        Schema::create('dry_rice_stocks', function (Blueprint $table) {
            $table->id();
            $table->string('stock_number')->unique();
            $table->date('stock_date');
            
            // Foreign keys
            $table->foreignId('drying_process_id')->constrained('drying_processes');
            $table->foreignId('variety_id')->constrained('varieties');
            $table->foreignId('warehouse_id')->constrained('warehouses');
            
            // Data
            $table->decimal('weight', 10, 2); // berat kering
            $table->decimal('moisture_content', 5, 2); // kadar air (%)
            $table->decimal('remaining_weight', 10, 2); // sisa stok yang belum di packing
            
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dry_rice_stocks');
    }
};
