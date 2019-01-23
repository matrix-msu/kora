<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePreferencesTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('preferences', function(Blueprint $table)
        {
            $table->engine = 'MyISAM';

            $table->increments('id');
            $table->unsignedInteger('user_id')->unique();
            $table->boolean('use_dashboard')->default(1);
            $table->unsignedTinyInteger('logo_target')->default(1);
            $table->unsignedTinyInteger('proj_page_tab_selection')->default(1);
            $table->unsignedTinyInteger('single_proj_page_tab_selection')->default(1);
            $table->boolean('onboarding')->default(1);
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');;
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('preferences');
    }

}
