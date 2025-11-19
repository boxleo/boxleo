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
        if (!Schema::hasColumn('performance_evaluations', 'unit_id')) {
            Schema::table('performance_evaluations', function (Blueprint $table) {
                $table->foreignId('unit_id')
                    ->nullable()
                    ->after('evaluator_id')
                    ->constrained('units')
                    ->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('performance_evaluations', 'unit_id')) {
            Schema::table('performance_evaluations', function (Blueprint $table) {
                $table->dropConstrainedForeignId('unit_id');
            });
        }
    }
};
