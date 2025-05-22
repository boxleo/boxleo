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
        Schema::create('payslips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('basic_pay', 10, 2);
            $table->decimal('gross_pay', 10, 2)->default(0.00);
            $table->decimal('total_deductions', 10, 2)->default(0.00);
            $table->decimal('net_pay', 10, 2)->default(0.00);
            $table->date('pay_date')->nullable();
            $table->integer('month');
            $table->integer('year');
            $table->string('payment_mode')->nullable();
            $table->string('bank')->nullable();
            $table->string('bank_branch')->nullable();
            $table->string('bank_account')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('payment_mode')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_branch')->nullable();
            $table->string('bank_account')->nullable();
            $table->string('mpesa_no')->nullable();
            $table->decimal('salary', 10, 2)->nullable();
            $table->decimal('house_allowance', 10, 2)->nullable();
            $table->decimal('transport_allowance', 10, 2)->nullable();
            $table->decimal('bonus', 10, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payslips');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'payment_mode',
                'bank_name',
                'bank_branch',
                'bank_account',
                'mpesa_no',
                'salary',
                'house_allowance',
                'transport_allowance',
                'bonus'
            ]);
        });
    }
};
