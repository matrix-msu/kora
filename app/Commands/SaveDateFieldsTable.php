<?php namespace App\Commands;

use App\DateField;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;

class SaveDateFieldsTable extends Command implements SelfHandling, ShouldQueue {

    /*
    |--------------------------------------------------------------------------
    | Save Date Fields Table
    |--------------------------------------------------------------------------
    |
    | This command handles the backup of the date fields table
    |
    */

    use InteractsWithQueue, SerializesModels;

    /**
     * Execute the command.
     */
    public function handle() {
        Log::info("Started backing up the Date Fields table.");

        $table_path = $this->backup_filepath . "/date_fields/";
        $table_array = $this->makeBackupTableArray("date_fields");
        if($table_array == false) { return;}

        $row_id = DB::table('backup_partial_progress')->insertGetId(
            $table_array
        );

        $this->backup_fs->makeDirectory($table_path);
        DateField::chunk(500, function($datefields) use ($table_path, $row_id) {
            $count = 0;
            $all_datefields_data = new Collection();

            foreach($datefields as $datefield) {
                $individual_datefield_data = new Collection();

                $individual_datefield_data->put("id", $datefield->id);
                $individual_datefield_data->put("rid", $datefield->rid);
                $individual_datefield_data->put("fid", $datefield->fid);
                $individual_datefield_data->put("flid", $datefield->flid);
                $individual_datefield_data->put("circa", $datefield->circa);
                $individual_datefield_data->put("month", $datefield->month);
                $individual_datefield_data->put("day", $datefield->year);
                $individual_datefield_data->put("year", $datefield->year);
                $individual_datefield_data->put("era", $datefield->era);
                $individual_datefield_data->put("date_object", $datefield->date_object);
                $individual_datefield_data->put("created_at", $datefield->created_at->toDateTimeString());
                $individual_datefield_data->put("updated_at", $datefield->updated_at->toDateTimeString());

                $all_datefields_data->push($individual_datefield_data);
                $count++;
            }

            DB::table("backup_partial_progress")->where("id", $row_id)->increment("progress", $count, ["updated_at" => Carbon::now()] );
            $increment = DB::table("backup_partial_progress")->where("id", $row_id)->pluck("progress");
            $this->backup_fs->put($table_path . $increment . ".json", json_encode($all_datefields_data));
        });
        DB::table("backup_overall_progress")->where("id", $this->backup_id)->increment("progress",1,["updated_at"=>Carbon::now()]);
    }
}