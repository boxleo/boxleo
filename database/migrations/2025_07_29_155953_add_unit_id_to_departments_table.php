<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUnitIdToDepartmentsTable extends Migration
{
    public function up()
    {
        Schema::table('departments', function (Blueprint $table) {
            if (!Schema::hasColumn('departments', 'unit_id')) {
                $table->unsignedBigInteger('unit_id')->nullable()->after('id');
                $table->foreign('unit_id')->references('id')->on('units')->onDelete('set null');
            }
        });

        // Backfill unit_id for existing departments (assuming all belong to unit_id 1 based on JSON)
        \App\Models\Department::whereNull('unit_id')->update(['unit_id' => 1]);
    }

    public function down()
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->dropColumn('unit_id');
        });
    }
}
