<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('user_departments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('department_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['user_id', 'department_id']); // Optional: to prevent duplicates
            $table->softDeletes();

        });
    }

    public function down()
    {
        Schema::dropIfExists('user_departments');
    }
};
