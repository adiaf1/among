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
        Schema::create('items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('category')->default('lainnya');
            $table->string('unit', 50);
            $table->foreignUuid('rice_variety_id')->nullable()->constrained('rice_varieties')->nullOnDelete();
            $table->foreignUuid('seed_class_id')->nullable()->constrained('seed_classes')->nullOnDelete();
            $table->decimal('minimum_stock', 12, 2)->default(0);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
