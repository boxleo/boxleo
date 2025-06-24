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
        Schema::create('deductions', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->string('deduction_type')->default('custom'); // e.g. 'insurance', 'loan', 'statutory'
            $table->boolean(column: 'is_recurring')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deductions');
    }

    // Accessor to ensure is_recurring is always returned as boolean
    public function getIsRecurringAttribute($value)
    {
        return (bool) $value;
    }
}

