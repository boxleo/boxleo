a<?php

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
        //

        Schema::table('payslips', function (Blueprint $table) {
            // Basic + Bonuses + Allowances	
            $table->decimal('gross_pay', 10, 2)->default(0.00)->after('basic_pay');
            $table->decimal('total_deductions', 10, 2)->default(0.00)->after('gross_pay');
            // Take-home after deductions	
            $table->decimal('net_pay', 10, 2)->default(0.00)->after('total_deductions');
            $table->integer('month')->after('pay_date');
            $table->integer('year')->after('month');
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
