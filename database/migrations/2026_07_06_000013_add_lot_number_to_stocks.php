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
        Schema::table('stocks', function (Blueprint $table) {
            $table->index('item_id');
            $table->index('warehouse_id');
            $table->dropUnique(['item_id', 'warehouse_id']);
            $table->string('lot_number', 100)->nullable()->after('warehouse_id');
            $table->index(['item_id', 'warehouse_id', 'lot_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stocks', function (Blueprint $table) {
            $table->dropIndex(['item_id', 'warehouse_id', 'lot_number']);
            $table->dropColumn('lot_number');
            $table->unique(['item_id', 'warehouse_id']);
            $table->dropIndex(['item_id']);
            $table->dropIndex(['warehouse_id']);
        });
    }
};
