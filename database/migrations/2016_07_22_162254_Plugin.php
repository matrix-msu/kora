<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PluginsTable extends Migration
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
            $table->integer('pid');
            $table->string('name');
            $table->boolean('active');
            $table->string('url');
            $table->timestamps();

            $table->foreign('pid')->references('pid')->on('projects')->onDelete('cascade');
        });

        Schema::create('plugin_settings', function(Blueprint $table)
        {
            $table->engine = 'MyISAM';

            $table->increments('id');
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
            $table->integer('gid');
            $table->timestamps();

            $table->foreign('plugin_id')->references('id')->on('plugins')->onDelete('cascade');
            $table->foreign('gid')->references('id')->on('project_groups')->onDelete('cascade');

            $table->index(['plugin_id', 'gid']);
        });

        Schema::create('plugin_menus', function(Blueprint $table)
        {
            $table->engine = 'MyISAM';

            $table->increments('id');
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
