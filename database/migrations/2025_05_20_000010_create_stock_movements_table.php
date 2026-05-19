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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->string('movement_number')->unique();
            $table->date('movement_date');
            
            // Foreign keys
            $table->foreignId('warehouse_id')->constrained('warehouses');
            $table->foreignId('variety_id')->constrained('varieties');
            
            // Stock reference (polymorphic)
            $table->string('stock_type'); // 'dry_rice' atau 'packed'
            $table->unsignedBigInteger('stock_id');
            
            // Movement type
            $table->string('movement_type'); // 'in', 'out', 'adjustment', 'packing'
            
            // Data
            $table->decimal('quantity_before', 10, 2);
            $table->decimal('quantity_change', 10, 2);
            $table->decimal('quantity_after', 10, 2);
            $table->string('unit'); // satuan
            
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
