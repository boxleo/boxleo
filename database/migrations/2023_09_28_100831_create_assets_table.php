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
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('category');
            $table->date('issuance_date')->nullable();
            $table->foreignId('office_id')->nullable();
            $table->foreignId('department_id')->nullable();
            $table->string('serial_no')->unique();
            $table->string('condition')->nullable();
            $table->string('warranty')->nullable();
            $table->string('comment')->nullable();
            $table->decimal('purchase_cost', 10, 2)->nullable();
            $table->boolean('is_assigned')->default(0);
            // $table->foreignId('issued_to')->nullable();
            $table->foreignId('issued_by')->nullable();
            $table->decimal('repair_cost', 10, 2)->default(0.00);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
