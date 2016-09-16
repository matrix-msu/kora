<?php namespace App\Commands;

use App\GeneratedListField;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldBeQueued;

class SaveGeneratedListFieldsTable extends Command implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /**
     * Execute the command.
     */
    public function handle() {
        Log::info("Started backing up the Generated List Fields table.");

        $table_path = $this->backup_filepath . "/generated_list_fields/";

        $row_id = DB::table('backup_partial_progress')->insertGetId(
            $this->makeBackupTableArray("generated_list_fields")
        );

        $this->backup_fs->makeDirectory($table_path);
        GeneratedListField::chunk(500, function($generatedlistfields) use ($table_path, $row_id) {
            $count = 0;
            $all_generatedlistfields_data = new Collection();

            foreach ($generatedlistfields as $generatedlistfield) {
                $individual_generatedlistfield_data = new Collection();

                $individual_generatedlistfield_data->put("id", $generatedlistfield->id);
                $individual_generatedlistfield_data->put("rid", $generatedlistfield->rid);
                $individual_generatedlistfield_data->put("flid", $generatedlistfield->flid);
                $individual_generatedlistfield_data->put("options", $generatedlistfield->options);
                $individual_generatedlistfield_data->put("created_at", $generatedlistfield->created_at->toDateTimeString());
                $individual_generatedlistfield_data->put("updated_at", $generatedlistfield->updated_at->toDateTimeString());

                $all_generatedlistfields_data->push($individual_generatedlistfield_data);
                $count++;
            }

            DB::table("backup_partial_progress")->where("id", $row_id)->increment("progress", $count, ["updated_at" => Carbon::now()] );
            $increment = DB::table("backup_partial_progress")->where("id", $row_id)->pluck("progress");
            $this->backup_fs->put($table_path . $increment . ".json", json_encode($all_generatedlistfields_data));
        });
        DB::table("backup_overall_progress")->where("id", $this->backup_id)->increment("progress",1,["updated_at"=>Carbon::now()]);
    }
}