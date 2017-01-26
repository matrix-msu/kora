<?php namespace App\Commands;

use Carbon\Carbon;
use App\RichTextField;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldBeQueued;

class SaveGeolocatorSupportTable extends Command implements SelfHandling, ShouldQueue
{
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
        DB::table('geolocator_support')->chunk(500, function($geosups) use ($table_path, $row_id) {
            $count = 0;
            $all_geolocatorsupport_data = new Collection();

            foreach($geosups as $geolocatorsupport) {
                $individual_geolocatorsupport_data = new Collection();

                $individual_geolocatorsupport_data->put("form_group_id", $geolocatorsupport->form_group_id);
                $individual_geolocatorsupport_data->put("user_id", $geolocatorsupport->user_id);

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