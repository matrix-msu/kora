<?php

namespace App\Commands;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SaveFormCustomTable extends Command implements ShouldQueue
{
    /*
    |--------------------------------------------------------------------------
    | Save Form Custom Table
    |--------------------------------------------------------------------------
    |
    | This command handles the backup of the form custom table
    |
    */

    use InteractsWithQueue, SerializesModels;

    /**
     * Execute the command.
     */
    public function handle() {
        Log::info("Started backing up the Form Custom table.");

        $table_path = $this->backup_filepath . "/form_custom/";
        $table_array = $this->makeBackupTableArray("form_custom");
        if($table_array == false) { return;}

        $row_id = DB::table('backup_partial_progress')->insertGetId(
            $table_array
        );

        $this->backup_fs->makeDirectory($table_path);
        DB::table("form_custom")->orderBy('id')->chunk(500, function($fcustoms) use ($table_path, $row_id) {
            $count = 0;
            $all_fcustoms_data = new Collection();

            foreach($fcustoms as $fcustom) {
                $fcustom_data = new Collection();

                $fcustom_data->put("id", $fcustom->id);
                $fcustom_data->put("uid", $fcustom->uid);
                $fcustom_data->put("pid", $fcustom->pid);
                $fcustom_data->put("fid", $fcustom->fid);
                $fcustom_data->put("sequence", $fcustom->sequence);
                $fcustom_data->put("created_at", $fcustom->created_at->toDateTimeString());
                $fcustom_data->put("updated_at", $fcustom->updated_at->toDateTimeString());

                $all_fcustoms_data->push($fcustom_data);
                $count++;
            }

            DB::table("backup_partial_progress")->where("id", $row_id)->increment("progress", $count, ["updated_at" => Carbon::now()] );
            $increment = DB::table("backup_partial_progress")->where("id", $row_id)->pluck("progress");
            $this->backup_fs->put($table_path . $increment . ".json", json_encode($all_fcustoms_data));
        });
        DB::table("backup_overall_progress")->where("id", $this->backup_id)->increment("progress",1,["updated_at"=>Carbon::now()]);
    }
}
