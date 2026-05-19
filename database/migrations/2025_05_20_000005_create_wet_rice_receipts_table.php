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
        Schema::create('wet_rice_receipts', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_number')->unique();
            $table->date('receipt_date');
            
            // Foreign keys
            $table->foreignId('farmer_id')->constrained('farmers');
            $table->foreignId('variety_id')->constrained('varieties');
            $table->foreignId('warehouse_id')->constrained('warehouses');
            $table->foreignId('unit_id')->constrained('units');
            
            // Data
            $table->decimal('gross_weight', 10, 2); // berat kotor
            $table->decimal('tare_weight', 10, 2)->default(0); // berat kemasan
            $table->decimal('net_weight', 10, 2); // berat bersih
            $table->decimal('moisture_content', 5, 2); // kadar air (%)
            $table->text('notes')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wet_rice_receipts');
    }
};
