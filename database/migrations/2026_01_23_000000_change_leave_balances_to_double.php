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
        Schema::table('leave_balances', function (Blueprint $table) {
            $table->double('balance', 8, 2)->change();
            $table->double('allocated', 8, 2)->change();
            $table->double('taken', 8, 2)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_balances', function (Blueprint $table) {
            $table->integer('balance')->change();
            $table->integer('allocated')->change();
            $table->integer('taken')->change();
        });
    }
};
