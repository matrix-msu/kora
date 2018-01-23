<?php namespace App\Commands;

use App\ModelField;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;

class SaveModelFieldsTable extends Command implements ShouldQueue {

    /*
    |--------------------------------------------------------------------------
    | Save Model Fields Table
    |--------------------------------------------------------------------------
    |
    | This command handles the backup of the model fields table
    |
    */

    use InteractsWithQueue, SerializesModels;

    /**
     * Execute the command.
     */
    public function handle() {
        Log::info("Started backing up the Model Fields table.");

        $table_path = $this->backup_filepath . "/model_fields/";
        $table_array = $this->makeBackupTableArray("model_fields");
        if($table_array == false) { return;}

        $row_id = DB::table('backup_partial_progress')->insertGetId(
            $table_array
        );

        $this->backup_fs->makeDirectory($table_path);
        ModelField::chunk(500, function($models) use ($table_path, $row_id) {
            $count = 0;
            $all_modelfields_data = new Collection();

            foreach($models as $mod) {
                $individual_modelfields_data = new Collection();

                $individual_modelfields_data->put("id", $mod->id);
                $individual_modelfields_data->put("rid", $mod->rid);
                $individual_modelfields_data->put("fid", $mod->fid);
                $individual_modelfields_data->put("flid", $mod->flid);
                $individual_modelfields_data->put("model", $mod->model);
                $individual_modelfields_data->put("created_at", $mod->created_at->toDateTimeString());
                $individual_modelfields_data->put("updated_at", $mod->updated_at->toDateTimeString());

                $all_modelfields_data->push($individual_modelfields_data);
                $count++;
            }

            DB::table('backup_partial_progress')->where('id',$row_id)->increment('progress',$count,['updated_at'=>Carbon::now()]);
            $increment = DB::table('backup_partial_progress')->where('id',$row_id)->pluck('progress');
            $this->backup_fs->put($table_path . $increment . ".json",json_encode($all_modelfields_data));
        });
        DB::table("backup_overall_progress")->where("id", $this->backup_id)->increment("progress",1,["updated_at"=>Carbon::now()]);
    }
}