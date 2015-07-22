<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function(Blueprint $table)
        {
            $table->increments('id');
            $table->boolean('admin');
            $table->boolean('active');
            $table->string('username')->unique();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password', 60);
            $table->string('organization');
            $table->string('language');
            $table->string('regtoken');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('users');
    }
}