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
        Schema::create('seed_growing_harvests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('seed_growing_id')->unique()->constrained('seed_growings')->cascadeOnDelete();
            $table->date('harvest_date')->nullable();
            $table->decimal('harvested_quantity', 12, 2)->default(0);
            $table->string('unit', 50)->default('kg');
            $table->string('material_state', 50)->nullable();
            $table->string('status', 50)->default('panen');
            $table->text('notes')->nullable();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seed_growing_harvests');
    }
};
