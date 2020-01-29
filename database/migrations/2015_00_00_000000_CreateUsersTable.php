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
            $table->string('username',20)->unique();
            $table->string('email', 60)->unique();
            $table->string('password', 60);
            $table->string('regtoken',100);
            $table->string('gitlab_token',100)->nullable()->unique();
            $table->jsonb('preferences')->nullable();
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