<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Plugin extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('plugins', function(Blueprint $table)
        {
            $table->engine = 'MyISAM';

            $table->increments('id');
            $table->string('name');
            $table->boolean('active');
            $table->string('url');
            $table->timestamps();

        });

        Schema::create('plugin_settings', function(Blueprint $table)
        {
            $table->engine = 'MyISAM';

            $table->integer('plugin_id');
            $table->string('option');
            $table->string('value');
            $table->timestamps();

            $table->foreign('plugin_id')->references('id')->on('plugins')->onDelete('cascade');
        });

        Schema::create('plugin_users', function(Blueprint $table)
        {
            $table->engine = 'MyISAM';

            $table->integer('plugin_id');
            $table->integer('uid');
            $table->timestamps();

            $table->foreign('plugin_id')->references('id')->on('plugins')->onDelete('cascade');
            $table->foreign('uid')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('plugin_menus', function(Blueprint $table)
        {
            $table->engine = 'MyISAM';

            $table->integer('plugin_id');
            $table->string('name');
            $table->string('url');
            $table->integer('order');
            $table->timestamps();

            $table->foreign('plugin_id')->references('id')->on('plugins')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('plugin_menus');
        Schema::drop('plugin_users');
        Schema::drop('plugin_settings');
        Schema::drop('plugins');
    }
}
