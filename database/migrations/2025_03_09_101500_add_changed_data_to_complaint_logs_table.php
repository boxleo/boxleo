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
        Schema::table('complaint_logs', function (Blueprint $table) {
            
            $table->json('changed_data')->nullable()->after('new_data');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('complaint_logs', function (Blueprint $table) {
            

            $table->dropColumn('changed_data');

        });
    }
};
