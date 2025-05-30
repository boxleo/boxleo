

// use Illuminate\Database\Migrations\Migration;
// use Illuminate\Database\Schema\Blueprint;
// use Illuminate\Support\Facades\Schema;

// return new class extends Migration
// {
//     /**
//      * Run the migrations.
//      */
//     public function up()
//     {
//         Schema::table('performance_evaluations', function (Blueprint $table) {
//             $table->unsignedBigInteger('unit_id')->nullable();
//             // Add foreign key if needed
//             $table->foreign('unit_id')->references('id')->on('units');
//         });
//     }

    /**
     * Reverse the migrations.
     */
//     public function down()
//     {
//         Schema::table('performance_evaluations', function (Blueprint $table) {
//             $table->dropForeign(['unit_id']);
//             $table->dropColumn('unit_id');
//         });
//     }
// };

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
        Schema::table('performance_evaluations', function (Blueprint $table) {
            $table->unsignedBigInteger('unit_id')->nullable()->after('user_id');
            
            // If you have a units table, add foreign key constraint
            // $table->foreign('unit_id')->references('id')->on('units')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('performance_evaluations', function (Blueprint $table) {
            // Drop foreign key first if it exists
            // $table->dropForeign(['unit_id']);
            $table->dropColumn('unit_id');
        });
    }
};