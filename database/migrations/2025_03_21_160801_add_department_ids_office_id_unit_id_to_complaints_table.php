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
        Schema::table('complaints', function (Blueprint $table) {
            // $table->json('department_ids')->nullable()->after('user_id'); // Allow multiple departments
            $table->foreignId('office_id')->nullable()->after('department_ids')->constrained('offices')->onDelete('set null');
            $table->foreignId('unit_id')->nullable()->after('office_id')->constrained('units')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            // $table->dropColumn('department_ids');
            $table->dropForeign(['office_id']);
            $table->dropColumn('office_id');
            $table->dropForeign(['unit_id']);
            $table->dropColumn('unit_id');
        });
    }
};
