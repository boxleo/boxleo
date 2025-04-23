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
        Schema::create('other_deductions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payslip_id')->constrained()->onDelete('cascade');
            $table->string('label');
            $table->decimal('amount', 10, 2);
            $table->timestamps();
            $table->softDeletes();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('other_deductions');
    }
};
