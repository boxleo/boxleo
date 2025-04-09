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
        Schema::create('announcements', function (Blueprint $table) {



            $table->id(); // Primary key

            $table->string('subject');

            $table->text('description');

            $table->unsignedBigInteger('author');

            $table->date('publish_date');

            $table->date('expiration_date')->nullable(); // Date when the announcement expires

            $table->boolean('is_active')->default(true); // Status of the announcement

            $table->string('attachment')->nullable(); // Path to any attachment

            $table->integer('priority')->default(0); // Priority level of the announcement

            $table->string('status')->default('draft'); // Status of the announcement

            $table->timestamps(); // Created at and updated at timestamps

            $table->softDeletes(); // Soft delete column



            // Foreign key constraint

            $table->foreign('author')->references('id')->on('users')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
