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
            $table->string('cost_type', 30)->default('per_kg')->after('cost_per_kg');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seed_production_steps', function (Blueprint $table) {
            $table->dropColumn('cost_type');
        });
    }
};
