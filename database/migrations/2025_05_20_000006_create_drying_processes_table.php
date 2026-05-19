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
        Schema::create('drying_processes', function (Blueprint $table) {
            $table->id();
            $table->string('process_number')->unique();
            $table->date('process_date');
            
            // Foreign keys
            $table->foreignId('wet_rice_receipt_id')->constrained('wet_rice_receipts');
            $table->foreignId('variety_id')->constrained('varieties');
            $table->foreignId('warehouse_id')->constrained('warehouses');
            
            // Data before drying
            $table->decimal('initial_weight', 10, 2); // berat awal basah
            $table->decimal('initial_moisture', 5, 2); // kadar air awal (%)
            
            // Data after drying
            $table->decimal('final_weight', 10, 2); // berat akhir kering
            $table->decimal('final_moisture', 5, 2); // kadar air akhir (%)
            $table->decimal('weight_loss', 10, 2)->default(0); // susut berat
            
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drying_processes');
    }
};
