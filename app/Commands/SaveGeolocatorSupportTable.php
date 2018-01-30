<?php namespace App\Commands;

use App\GeolocatorField;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;

class SaveGeolocatorSupportTable extends Command implements ShouldQueue {

    /*
    |--------------------------------------------------------------------------
    | Save Geolocator Support Table
    |--------------------------------------------------------------------------
    |
    | This command handles the backup of the geolocator support table
    |
    */

    use InteractsWithQueue, SerializesModels;

    /**
     * Execute the command.
     */
    public function handle() {
        Log::info("Started backing up the Geolocator Support table.");

        $table_path = $this->backup_filepath . "/geolocator_support/";
        $table_array = $this->makeBackupTableArray("geolocator_support");
        if($table_array == false) { return;}

        $row_id = DB::table('backup_partial_progress')->insertGetId(
            $table_array
        );

        $this->backup_fs->makeDirectory($table_path);
        DB::table(GeolocatorField::SUPPORT_NAME)->orderBy('id')->chunk(500, function($geosups) use ($table_path, $row_id) {
            $count = 0;
            $all_geolocatorsupport_data = new Collection();

            foreach($geosups as $geolocatorsupport) {
                $individual_geolocatorsupport_data = new Collection();

                $individual_geolocatorsupport_data->put("id", $geolocatorsupport->id);
                $individual_geolocatorsupport_data->put("fid", $geolocatorsupport->fid);
                $individual_geolocatorsupport_data->put("rid", $geolocatorsupport->rid);
                $individual_geolocatorsupport_data->put("flid", $geolocatorsupport->flid);
                $individual_geolocatorsupport_data->put("desc", $geolocatorsupport->desc);
                $individual_geolocatorsupport_data->put("lat", $geolocatorsupport->lat);
                $individual_geolocatorsupport_data->put("lon", $geolocatorsupport->lon);
                $individual_geolocatorsupport_data->put("zone", $geolocatorsupport->zone);
                $individual_geolocatorsupport_data->put("easting", $geolocatorsupport->easting);
                $individual_geolocatorsupport_data->put("northing", $geolocatorsupport->northing);
                $individual_geolocatorsupport_data->put("address", $geolocatorsupport->address);
                $individual_geolocatorsupport_data->put("created_at", $geolocatorsupport->created_at); // Don't format, already string.
                $individual_geolocatorsupport_data->put("updated_at", $geolocatorsupport->updated_at);

                $all_geolocatorsupport_data->push($individual_geolocatorsupport_data);
                $count++;
            }

            DB::table('backup_partial_progress')->where('id',$row_id)->increment('progress',$count,['updated_at'=>Carbon::now()]);
            $increment = DB::table('backup_partial_progress')->where('id',$row_id)->pluck('progress');
            $this->backup_fs->put($table_path . $increment . ".json",json_encode($all_geolocatorsupport_data));
        });
        DB::table("backup_overall_progress")->where("id", $this->backup_id)->increment("progress",1,["updated_at"=>Carbon::now()]);
    }
}