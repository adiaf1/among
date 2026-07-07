<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('seed_production_steps')
            ->where('stage', 'panen')
            ->delete();

        DB::table('seed_production_steps')
            ->where('sort_order', '>', 1)
            ->decrement('sort_order');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
