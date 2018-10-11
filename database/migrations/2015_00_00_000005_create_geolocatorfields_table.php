<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateGeolocatorfieldsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('geolocator_fields', function(Blueprint $table)
		{
			$table->engine = 'MyISAM';

			$table->increments('id');

			$table->integer('rid')->unsigned();
			$table->integer('fid')->unsigned();
			$table->integer('flid')->unsigned();
			$table->timestamps();

			$table->foreign('rid')->references('rid')->on('records')->onDelete('cascade');
			$table->foreign('flid')->references('flid')->on('fields')->onDelete('cascade');
		});

		Schema::create('geolocator_support', function(Blueprint $table)
		{
			$table->engine = 'MyISAM';

			$table->increments('id');
			$table->integer('fid')->unsigned();
			$table->integer('rid')->unsigned();
			$table->integer('flid')->unsigned();
			$table->string('desc');
			$table->float('lat', 10, 7); // Millimeter precision for Lat/Lon coordinates.
			$table->float('lon', 10, 7);
			$table->string('zone');
			$table->float('easting', 10, 3); // Millimeter precision for UTM.
			$table->float('northing', 10, 3);
			$table->text('address');
			$table->timestamps();
		});

		DB::statement("ALTER TABLE ". config('database.connections.mysql.prefix') ."geolocator_support ADD FULLTEXT search_geo_desc(`desc`)");
		DB::statement("ALTER TABLE ". config('database.connections.mysql.prefix') ."geolocator_support ADD FULLTEXT search_geo_address(`address`)");
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('geolocator_support');
		Schema::drop('geolocator_fields');
	}

}
