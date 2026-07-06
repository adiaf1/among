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
        Schema::table('seed_growing_harvests', function (Blueprint $table) {
            $table->foreignUuid('harvest_item_id')->nullable()->after('seed_growing_id')->constrained('items')->nullOnDelete();
            $table->foreignUuid('harvest_warehouse_id')->nullable()->after('harvest_item_id')->constrained('warehouses')->nullOnDelete();
            $table->foreignUuid('stock_id')->nullable()->after('harvest_warehouse_id')->constrained('stocks')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seed_growing_harvests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('stock_id');
            $table->dropConstrainedForeignId('harvest_warehouse_id');
            $table->dropConstrainedForeignId('harvest_item_id');
        });
    }
};
