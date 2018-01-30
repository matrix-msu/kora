<?php

namespace App\Commands;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SaveProjectCustomTable extends Command implements ShouldQueue
{
    /*
    |--------------------------------------------------------------------------
    | Save Project Custom Table
    |--------------------------------------------------------------------------
    |
    | This command handles the backup of the project custom table
    |
    */

    use InteractsWithQueue, SerializesModels;

    /**
     * Execute the command.
     */
    public function handle() {
        Log::info("Started backing up the Project Custom table.");

        $table_path = $this->backup_filepath . "/project_custom/";
        $table_array = $this->makeBackupTableArray("project_custom");
        if($table_array == false) { return;}

        $row_id = DB::table('backup_partial_progress')->insertGetId(
            $table_array
        );

        $this->backup_fs->makeDirectory($table_path);
        DB::table("project_custom")->orderBy('id')->chunk(500, function($pcustoms) use ($table_path, $row_id) {
            $count = 0;
            $all_pcustoms_data = new Collection();

            foreach($pcustoms as $pcustom) {
                $pcustom_data = new Collection();

                $pcustom_data->put("id", $pcustom->id);
                $pcustom_data->put("uid", $pcustom->uid);
                $pcustom_data->put("pid", $pcustom->pid);
                $pcustom_data->put("sequence", $pcustom->sequence);
                $pcustom_data->put("created_at", $pcustom->created_at->toDateTimeString());
                $pcustom_data->put("updated_at", $pcustom->updated_at->toDateTimeString());

                $all_pcustoms_data->push($pcustom_data);
                $count++;
            }

            DB::table("backup_partial_progress")->where("id", $row_id)->increment("progress", $count, ["updated_at" => Carbon::now()] );
            $increment = DB::table("backup_partial_progress")->where("id", $row_id)->pluck("progress");
            $this->backup_fs->put($table_path . $increment . ".json", json_encode($all_pcustoms_data));
        });
        DB::table("backup_overall_progress")->where("id", $this->backup_id)->increment("progress",1,["updated_at"=>Carbon::now()]);
    }
}
