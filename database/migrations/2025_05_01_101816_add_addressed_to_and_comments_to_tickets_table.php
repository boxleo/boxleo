<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAddressedToAndCommentsToTicketsTable extends Migration
{
    public function up()
    {
        Schema::table('tickets', function (Blueprint $table) {
            // this will create an unsigned BIGINT addressed_to column, nullable,
            // and set up a foreign key constraint to users.id
            $table->foreignId('addressed_to')
                  ->nullable()
                  ->constrained('users')
                  ->after('user_id');

            // if you don’t already have a comments column
            $table->text('comments')
                  ->nullable()
                  ->after('closed_date');

            // if you don’t have a resolution column
            $table->text('resolution')
                  ->nullable()
                  ->after('comments');
        });
    }

    public function down()
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropForeign(['addressed_to']);
            $table->dropColumn(['addressed_to', 'comments', 'resolution']);
        });
    }
}