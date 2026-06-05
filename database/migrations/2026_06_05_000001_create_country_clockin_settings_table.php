<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('country_clockin_settings', function (Blueprint $table) {
            $table->id();
            $table->string('country');
            $table->string('country_code', 5);
            $table->time('default_clockin_time');        // e.g. 08:00
            $table->time('default_clockout_time');       // e.g. 17:00
            $table->integer('grace_minutes')->default(5); // minutes after clockin before marked Late
            $table->string('timezone')->default('Africa/Nairobi');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('country_clockin_overrides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_clockin_setting_id')
                  ->constrained('country_clockin_settings')->onDelete('cascade');
            $table->date('override_date');
            $table->time('clockin_time');
            $table->time('clockout_time')->nullable();
            $table->string('reason')->nullable();        // e.g. "Madaraka Day holiday"
            $table->enum('type', ['temporary', 'permanent'])->default('temporary');
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('country_clockin_overrides');
        Schema::dropIfExists('country_clockin_settings');
    }
};
