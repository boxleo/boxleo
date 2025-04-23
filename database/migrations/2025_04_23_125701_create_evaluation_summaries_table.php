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
        Schema::create('evaluation_summaries', function (Blueprint $table) {


            $table->id();
            $table->foreignId('appraisal_id')->constrained('appraisals')->onDelete('cascade');
            $table->text('objectives_met')->nullable();
            $table->text('improvement_areas')->nullable();
            $table->unsignedTinyInteger('overall_rating')->nullable();
            $table->timestamps();
            $table->softDeletes();

           
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluation_summaries');
    }
};
