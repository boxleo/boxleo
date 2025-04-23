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
        Schema::create('statutory_deductions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payslip_id')->constrained()->onDelete('cascade');
            $table->decimal('nhif', 10, 2)->default(0.00);
            $table->decimal('nssf', 10, 2)->default(0.00);
            $table->decimal('income_tax', 10, 2)->default(0.00);
            $table->decimal('tax_relief', 10, 2)->default(0.00);
            $table->decimal('paye', 10, 2)->default(0.00);
            $table->decimal('housing_levy', 10, 2)->default(0.00);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('statutory_deductions');
    }
};
