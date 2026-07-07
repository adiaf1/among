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
        Schema::create('seed_production_steps', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('seed_production_id')->constrained('seed_productions')->cascadeOnDelete();
            $table->unsignedTinyInteger('sort_order');
            $table->string('stage', 100);
            $table->string('label', 150);
            $table->date('planned_date')->nullable();
            $table->date('actual_date')->nullable();
            $table->decimal('cost', 13, 2)->default(0);
            $table->string('status', 50)->default('terjadwal');
            $table->text('notes')->nullable();
            $table->foreignUuid('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['seed_production_id', 'stage']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seed_production_steps');
    }
};
