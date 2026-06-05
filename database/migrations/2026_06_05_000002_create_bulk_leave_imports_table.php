<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bulk_leave_imports', function (Blueprint $table) {
            $table->id();
            $table->string('label')->nullable();
            $table->string('country')->nullable();       // null = all countries
            $table->integer('leave_type_id')->nullable();
            $table->double('days')->default(0);
            $table->string('action')->default('add');    // add | set
            $table->integer('total_records')->default(0);
            $table->integer('success_count')->default(0);
            $table->integer('failed_count')->default(0);
            $table->json('errors')->nullable();
            $table->enum('status', ['pending','processing','completed','failed'])->default('pending');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bulk_leave_imports');
    }
};
