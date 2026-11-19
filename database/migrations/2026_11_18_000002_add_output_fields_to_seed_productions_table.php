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
        Schema::table('seed_productions', function (Blueprint $table) {
            $table->foreignUuid('output_item_id')->nullable()->after('seed_class_id')->constrained('items')->nullOnDelete();
            $table->foreignUuid('output_stock_id')->nullable()->after('output_warehouse_id')->constrained('stocks')->nullOnDelete();
            $table->date('output_date')->nullable()->after('production_date');
            $table->decimal('output_quantity', 12, 2)->nullable()->after('target_quantity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seed_productions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('output_item_id');
            $table->dropConstrainedForeignId('output_stock_id');
            $table->dropColumn(['output_date', 'output_quantity']);
        });
    }
};
