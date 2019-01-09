<?php namespace App\Commands;

use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;

class SaveScriptsTable extends Command implements ShouldQueue {

    /*
    |--------------------------------------------------------------------------
    | Save Scripts Table
    |--------------------------------------------------------------------------
    |
    | This command handles the backup of the scripts table
    |
    */

    use InteractsWithQueue, SerializesModels;

    /**
     * Execute the command.
     */
    public function handle() {
        Log::info("Started backing up the Scripts table.");

        $table_path = $this->backup_filepath . "/scripts/";
        $table_array = $this->makeBackupTableArray("scripts");
        if($table_array == false) { return;}

        $row_id = DB::table('backup_partial_progress')->insertGetId(
            $table_array
        );

        //We don't save the sysadmin row. If we ever needed to restore this row, we couldn't get to the backup page
        DB::table('backup_partial_progress')->where('id',$row_id)->decrement("overall",1);

        $this->backup_fs->makeDirectory($table_path);
        DB::table('scripts')->orderBy('id')->chunk(500, function($scripts) use ($table_path, $row_id) {
            $count = 0;
            $all_scripts_data = new Collection();

            foreach($scripts as $script) {
                $individual_scripts_data = new Collection();

                $individual_scripts_data->put("id", $script->id);
                $individual_scripts_data->put("filename", $script->filename);
                $individual_scripts_data->put("hasRun", $script->hasRun);
                $individual_scripts_data->put("created_at", $script->created_at->toDateTimeString());
                $individual_scripts_data->put("updated_at", $script->updated_at->toDateTimeString());

                $all_scripts_data->push($individual_scripts_data);
                $count++;
            }

            DB::table("backup_partial_progress")->where("id", $row_id)->increment("progress", $count, ["updated_at" => Carbon::now()] );
            $increment = DB::table("backup_partial_progress")->where("id", $row_id)->pluck("progress");
            $this->backup_fs->put($table_path . $increment . ".json", json_encode($all_scripts_data));
        });
        DB::table("backup_overall_progress")->where("id", $this->backup_id)->increment("progress",1,["updated_at"=>Carbon::now()]);
    }
}