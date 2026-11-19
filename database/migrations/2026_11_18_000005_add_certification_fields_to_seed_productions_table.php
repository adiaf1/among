<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seed_productions', function (Blueprint $table) {
            $table->date('lab_test_date')->nullable()->after('output_date');
            $table->decimal('moisture_content', 5, 2)->nullable()->after('lab_test_date');
            $table->decimal('purity_percentage', 5, 2)->nullable()->after('moisture_content');
            $table->decimal('germination_rate', 5, 2)->nullable()->after('purity_percentage');
            $table->string('lab_result', 30)->nullable()->after('germination_rate');
            $table->string('certificate_number', 100)->nullable()->after('lab_result');
            $table->string('label_serial_range', 150)->nullable()->after('certificate_number');
            $table->unsignedInteger('label_count')->nullable()->after('label_serial_range');
            $table->date('label_expiry_date')->nullable()->after('label_count');
            $table->text('certification_notes')->nullable()->after('label_expiry_date');
        });
    }

    public function down(): void
    {
        Schema::table('seed_productions', function (Blueprint $table) {
            $table->dropColumn([
                'lab_test_date',
                'moisture_content',
                'purity_percentage',
                'germination_rate',
                'lab_result',
                'certificate_number',
                'label_serial_range',
                'label_count',
                'label_expiry_date',
                'certification_notes',
            ]);
        });
    }
};
