<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('earnings', function (Blueprint $table) {


            $table->id();
            //$table->foreignId('payslip_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('label');
            $table->decimal('amount', 10, 2);
            $table->boolean('is_recurring')->default(false);
            $table->enum('frequency', ['monthly', 'weekly']);
            $table->boolean(
'is_taxable')->default(true);
            $table->timestamps();
             $table->softDeletes();


            //  affects_nhif BOOLEAN DEFAULT TRUE,
            // affects_nssf BOOLEAN DEFAULT TRUE,
            // affects_paye BOOLEAN DEFAULT TRUE,
            // description TEXT,                


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('earnings');
    }
};
