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
        Schema::create('seed_growing_inspections', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('seed_growing_id')->constrained('seed_growings')->cascadeOnDelete();
            $table->string('stage', 50);
            $table->date('planned_date')->nullable();
            $table->date('actual_date')->nullable();
            $table->decimal('cost', 15, 2)->default(0);
            $table->string('status', 50)->default('terjadwal');
            $table->text('notes')->nullable();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['seed_growing_id', 'stage']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seed_growing_inspections');
    }
};
