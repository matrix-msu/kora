<?php namespace App\Commands;

use App\Form;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldBeQueued;

class SaveFormsTable extends Command implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /**
     * Execute the command.
     */
    public function handle() {
        Log::info("Started backing up the Forms table.");

        $table_path = $this->backup_filepath . "/forms/";
        $table_array = $this->makeBackupTableArray("forms");
        if($table_array == false) { return;}

        $row_id = DB::table('backup_partial_progress')->insertGetId(
            $table_array
        );

        $this->backup_fs->makeDirectory($table_path);
        Form::chunk(500, function($forms) use ($table_path, $row_id) {
            $count = 0;
            $all_forms_data = new Collection();

            foreach ($forms as $form) {
                $form_data = new Collection();

                $form_data->put("fid", $form->fid);
                $form_data->put("pid", $form->pid);
                $form_data->put("adminGID", $form->adminGID);
                $form_data->put("name", $form->name);
                $form_data->put("slug", $form->slug);
                $form_data->put("description", $form->description);
                $form_data->put("layout", $form->layout);
                $form_data->put("preset", $form->preset);
                $form_data->put("public_metadata", $form->public_metadata);
                $form_data->put("created_at", $form->created_at->toDateTimeString());
                $form_data->put("updated_at", $form->updated_at->toDateTimeString());

                $all_forms_data->push($form_data);
                $count++;
            }

            DB::table("backup_partial_progress")->where("id", $row_id)->increment("progress", $count, ["updated_at" => Carbon::now()] );
            $increment = DB::table("backup_partial_progress")->where("id", $row_id)->pluck("progress");
            $this->backup_fs->put($table_path . $increment . ".json", json_encode($all_forms_data));
        });
        DB::table("backup_overall_progress")->where("id", $this->backup_id)->increment("progress",1,["updated_at"=>Carbon::now()]);
    }
}