<?php namespace App\Commands;

use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;

class SaveGlobalCacheTable extends Command implements ShouldQueue {

    /*
    |--------------------------------------------------------------------------
    | Save Global Cache Table
    |--------------------------------------------------------------------------
    |
    | This command handles the backup of the global cache table
    |
    */

    use InteractsWithQueue, SerializesModels;

    /**
     * Execute the command.
     */
    public function handle() {
        Log::info("Started backing up the Global Cache table.");

        $table_path = $this->backup_filepath . "/global_cache/";
        $table_array = $this->makeBackupTableArray("global_cache");
        if($table_array == false) { return;}

        $row_id = DB::table('backup_partial_progress')->insertGetId(
            $table_array
        );

        //We don't save the sysadmin row. If we ever needed to restore this row, we couldn't get to the backup page
        DB::table('backup_partial_progress')->where('id',$row_id)->decrement("overall",1);

        $this->backup_fs->makeDirectory($table_path);
        DB::table('global_cache')->orderBy('id')->chunk(500, function($globalCache) use ($table_path, $row_id) {
            $count = 0;
            $all_global_cache_data = new Collection();

            foreach($globalCache as $globalcache) {
                $individual_global_cache_data = new Collection();

                $individual_global_cache_data->put("id", $globalcache->id);
                $individual_global_cache_data->put("user_id", $globalcache->user_id);
                $individual_global_cache_data->put("html", $globalcache->html);
                $individual_global_cache_data->put("created_at", $globalcache->created_at->toDateTimeString());
                $individual_global_cache_data->put("updated_at", $globalcache->updated_at->toDateTimeString());

                $all_global_cache_data->push($individual_global_cache_data);
                $count++;
            }

            DB::table("backup_partial_progress")->where("id", $row_id)->increment("progress", $count, ["updated_at" => Carbon::now()] );
            $increment = DB::table("backup_partial_progress")->where("id", $row_id)->pluck("progress");
            $this->backup_fs->put($table_path . $increment . ".json", json_encode($all_global_cache_data));
        });
        DB::table("backup_overall_progress")->where("id", $this->backup_id)->increment("progress",1,["updated_at"=>Carbon::now()]);
    }
}