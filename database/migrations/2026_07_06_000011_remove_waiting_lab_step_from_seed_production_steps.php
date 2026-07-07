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
            ->where('stage', 'menunggu_hasil_lab')
            ->delete();

        DB::table('seed_production_steps')
            ->where('sort_order', '>', 7)
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
