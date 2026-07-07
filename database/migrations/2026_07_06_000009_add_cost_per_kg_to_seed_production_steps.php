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
        Schema::table('seed_production_steps', function (Blueprint $table) {
            $table->decimal('quantity', 12, 2)->nullable()->after('actual_date');
            $table->decimal('cost_per_kg', 13, 2)->nullable()->after('quantity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seed_production_steps', function (Blueprint $table) {
            $table->dropColumn(['quantity', 'cost_per_kg']);
        });
    }
};
