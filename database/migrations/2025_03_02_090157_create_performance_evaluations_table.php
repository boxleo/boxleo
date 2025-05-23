<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{


    // public function up(): void
    // {
    //     Schema::create('performance_evaluations', function (Blueprint $table) {
    //         $table->id();
    //         $table->foreignId('user_id')
    //             ->constrained()
    //             ->onDelete('cascade'); // Employee being rated

    //         $table->foreignId('evaluator_id')
    //             ->nullable()
    //             ->constrained('users')
    //             ->nullOnDelete(); // Evaluator (the "doer" of the rating)

    //         $table->foreignId('department_id')
    //             ->nullable()
    //             ->constrained()
    //             ->nullOnDelete();

    //         $table->foreignId('unit_id')
    //             ->nullable()
    //             ->constrained()
    //             ->nullOnDelete();

    //         $table->date('evaluation_date')->nullable(); // The date or period of evaluation

    public function up(): void
    {
        Schema::create('performance_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained()
                ->onDelete('cascade'); // Employee being rated

            $table->foreignId('evaluator_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete(); // Evaluator (the "doer" of the rating)

            $table->foreignId('department_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('unit_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->date('evaluation_date')->nullable(); // The date or period of evaluation

            // Performance Metrics
            $table->integer('attendance')->default(0);
            $table->integer('problems_solved')->default(0);
            $table->integer('reports_submitted')->default(0);
            $table->integer('knowledge_of_work')->default(0);
            $table->integer('team_work')->default(0);
            $table->integer('reliability_visibility')->default(0);
            $table->integer('productivity')->default(0);
            $table->integer('discipline')->default(0);
            $table->integer('quality_of_work')->default(0);
            $table->integer('communication')->default(0);
            $table->float('total_score')->default(0); // Total out of 10
            $table->float('percentage')->default(0);  // Percentage out of 100

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
//     

public function down(): void
    {
        Schema::table('performance_evaluations', function (Blueprint $table) {
            if (Schema::hasColumn('performance_evaluations', 'unit_id')) {
                $table->dropForeign(['unit_id']);
                $table->dropColumn('unit_id');
            }
        });
    }
};