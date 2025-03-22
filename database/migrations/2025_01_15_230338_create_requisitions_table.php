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
        Schema::create('requisitions', function (Blueprint $table) {


            $table->id(); 
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete()->nullable();  
            $table->foreignId('department_id')->constrained()->cascadeOnDelete()->nullable(); 
            $table->string('status');
            $table->text('special_instructions')->nullable(); // Additional instructions
            $table->decimal('budgeted_expenses', 15, 2)->nullable(); // Estimated budgeted amount
            $table->decimal('funds_available', 15, 2)->nullable(); // Available funds
            // Approval flags 
            $table->boolean('is_line_manager')->default(0);
            $table->boolean('is_coo')->default(0);
            $table->boolean('is_hr')->default(0);
            $table->boolean('is_finance_manager')->default(0);
            $table->boolean('is_cfo')->default(0);
            $table->timestamps();
            $table->softDeletes();
        
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requisitions');
    }
};
