<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {

        Log::info('Running units migration update...');
        Schema::table('units', function (Blueprint $table) {
            $table->string('timezone')->default('UTC')->after('name');
            $table->time('work_start_time')->nullable()->after('timezone');
            $table->time('late_threshold')->nullable()->after('work_start_time');
            $table->string('weekend_day')->nullable()->after('late_threshold');
            $table->time('weekend_clock_in_time')->nullable()->after('weekend_day');
            $table->time('weekend_clock_out_time')->nullable()->after('weekend_clock_in_time');
            $table->time('weekday_threshold')->nullable()->after('weekend_clock_out_time');
            $table->time('weekend_threshold')->nullable()->after('weekday_threshold');
            $table->time('clock_in_time')->nullable()->after('weekend_threshold');
            $table->time('clock_out_time')->nullable()->after('clock_in_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $table->dropColumn([
                'timezone',
                'work_start_time',
                'late_threshold',
                'weekend_day',
                'weekend_clock_in_time',
                'weekend_clock_out_time',
                'weekday_threshold',
                'weekend_threshold',
                'clock_in_time',
                'clock_out_time',
            ]);
        });
    }
};
