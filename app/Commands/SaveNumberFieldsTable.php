<?php namespace App\Commands;

use App\NumberField;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldBeQueued;


class SaveNumberFieldsTable extends Command implements SelfHandling, ShouldBeQueued
{
    use InteractsWithQueue, SerializesModels;

    /**
     * Execute the command.
     */
    public function handle() {
        Log::info("Started backing up the Number Fields table.");

        $table_path = $this->backup_filepath . "/number_fields/";

        $row_id = DB::table('backup_partial_progress')->insertGetId(
            $this->makeBackupTableArray("number_fields")
        );

        $this->backup_fs->makeDirectory($table_path);
        NumberField::chunk(1000, function($numfields) use ($table_path, $row_id) {
            $count = 0;
            $all_numberfields_data = new Collection();

            foreach ($numfields as $numberfield) {
                $individual_numberfield_data = new Collection();

                $individual_numberfield_data->put("id", $numberfield->id);
                $individual_numberfield_data->put("rid", $numberfield->rid);
                $individual_numberfield_data->put("flid", $numberfield->flid);
                $individual_numberfield_data->put("number", $numberfield->number);
                $individual_numberfield_data->put("created_at", $numberfield->created_at->toDateTimeString());
                $individual_numberfield_data->put("updated_at", $numberfield->updated_at->toDateTimeString());

                $all_numberfields_data->push($individual_numberfield_data);
                $count++;
            }

            DB::table('backup_partial_progress')->where('id',$row_id)->increment('progress',$count,['updated_at'=>Carbon::now()]);
            $increment = DB::table('backup_partial_progress')->where('id',$row_id)->pluck('progress');
            $this->backup_fs->put($table_path . $increment . ".json",json_encode($all_numberfields_data));
            
        });
        DB::table("backup_overall_progress")->where("id", $this->backup_id)->increment("progress",1,["updated_at"=>Carbon::now()]);
    }
}