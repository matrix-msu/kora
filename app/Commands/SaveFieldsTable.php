<?php namespace App\Commands;

use App\Field;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldBeQueued;

class SaveFieldsTable extends Command implements SelfHandling, ShouldBeQueued
{
    use InteractsWithQueue, SerializesModels;

    /**
     * Execute the command.
     */
    public function handle() {
        Log::info("Started backing up the Fields table.");

        $table_path = $this->backup_filepath . "/fields/";

        $row_id = DB::table("backup_partial_progress")->insertGetId(
            $this->makeBackupTableArray("fields")
        );

        $this->backup_fs->makeDirectory($table_path);
        Field::chunk(1000, function($fields) use ($table_path, $row_id) {
            $count = 0;
            $all_fields_data = new Collection();

            foreach ($fields as $field) {
                $individual_field_data = new Collection();

                $individual_field_data->put("flid", $field->flid);
                $individual_field_data->put("pid", $field->pid);
                $individual_field_data->put("fid", $field->fid);
                $individual_field_data->put("order", $field->order);
                $individual_field_data->put("type", $field->type);
                $individual_field_data->put("name", $field->name);
                $individual_field_data->put("slug", $field->slug);
                $individual_field_data->put("desc", $field->desc);
                $individual_field_data->put("required", $field->required);
                $individual_field_data->put("default", $field->default);
                $individual_field_data->put("options", $field->options);
                $individual_field_data->put("created_at", $field->created_at->toDateTimeString());
                $individual_field_data->put("updated_at", $field->updated_at->toDateTimeString());

                $all_fields_data->push($individual_field_data);
                $count++;
            }

            DB::table("backup_partial_progress")->where("id", $row_id)->increment("progress", $count, ["updated_at" => Carbon::now()] );
            $increment = DB::table("backup_partial_progress")->where("id", $row_id)->pluck("progress");
            $this->backup_fs->put($table_path . $increment . ".json", json_encode($all_fields_data));
        });
        DB::table("backup_overall_progress")->where("id", $this->backup_id)->increment("progress",1,["updated_at"=>Carbon::now()]);
    }
}