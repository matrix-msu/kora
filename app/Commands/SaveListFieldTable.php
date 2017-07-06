<?php namespace App\Commands;

use App\ListField;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;

class SaveListFieldTable extends Command implements SelfHandling, ShouldQueue {

    /*
    |--------------------------------------------------------------------------
    | Save List Field Table
    |--------------------------------------------------------------------------
    |
    | This command handles the backup of the list field table
    |
    */

    use InteractsWithQueue, SerializesModels;

    /**
     * Execute the command.
     */
    public function handle() {
        Log::info("Started backing up the List Fields table.");

        $table_path = $this->backup_filepath . "/list_fields/";
        $table_array = $this->makeBackupTableArray("list_fields");
        if($table_array == false) { return;}

        $row_id = DB::table('backup_partial_progress')->insertGetId(
            $table_array
        );

        $this->backup_fs->makeDirectory($table_path);
        ListField::chunk(500, function($listfields) use ($table_path, $row_id) {
            $count = 0;
            $all_listfields_data = new Collection();

            foreach($listfields as $listfield) {
                $individual_listfield_data = new Collection();

                $individual_listfield_data->put("id", $listfield->id);
                $individual_listfield_data->put("rid", $listfield->rid);
                $individual_listfield_data->put("fid", $listfield->fid);
                $individual_listfield_data->put("flid", $listfield->flid);
                $individual_listfield_data->put("option", $listfield->option);
                $individual_listfield_data->put("created_at", $listfield->created_at->toDateTimeString());
                $individual_listfield_data->put("updated_at", $listfield->updated_at->toDateTimeString());

                $all_listfields_data->push($individual_listfield_data);
                $count++;
            }

            DB::table("backup_partial_progress")->where("id", $row_id)->increment("progress", $count, ["updated_at" => Carbon::now()] );
            $increment = DB::table("backup_partial_progress")->where("id", $row_id)->pluck("progress");
            $this->backup_fs->put($table_path . $increment . ".json", json_encode($all_listfields_data));
        });
        DB::table("backup_overall_progress")->where("id", $this->backup_id)->increment("progress",1,["updated_at"=>Carbon::now()]);
    }
}