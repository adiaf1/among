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
        Schema::create('sales', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('number', 100)->unique();
            $table->date('sale_date');
            $table->foreignUuid('customer_id')->constrained('customers')->restrictOnDelete();
            $table->string('payment_status', 50)->default('lunas');
            $table->string('transport_type', 100)->nullable();
            $table->string('vehicle_plate_number', 20)->nullable();
            $table->decimal('total_amount', 15, 2)->default(0);
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
        Schema::dropIfExists('sales');
    }
};
