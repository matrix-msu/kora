<?php namespace App\Commands;

use App\FormGroup;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldBeQueued;

class SaveFormGroupsTable extends Command implements SelfHandling, ShouldQueue {

    use InteractsWithQueue, SerializesModels;

    /**
     * Execute the command.
     */
    public function handle() {
        Log::info("Started backing up the Form Groups table.");

        $table_path = $this->backup_filepath . "/form_groups/";
        $table_array = $this->makeBackupTableArray("form_groups");
        if($table_array == false) { return;}

        $row_id = DB::table("backup_partial_progress")->insertGetId(
            $table_array
        );

        $this->backup_fs->makeDirectory($table_path);
        FormGroup::chunk(500, function($formgroups) use ($table_path, $row_id) {
            $count = 0;
            $all_formgroup_data = new Collection();

            foreach($formgroups as $formgroup) {
                $individual_formgroup_data = new Collection();

                $individual_formgroup_data->put("id", $formgroup->id);
                $individual_formgroup_data->put("name", $formgroup->name);
                $individual_formgroup_data->put("fid", $formgroup->fid);
                $individual_formgroup_data->put("create", $formgroup->create);
                $individual_formgroup_data->put("edit", $formgroup->edit);
                $individual_formgroup_data->put("delete", $formgroup->delete);
                $individual_formgroup_data->put("ingest", $formgroup->ingest);
                $individual_formgroup_data->put("modify", $formgroup->modify);
                $individual_formgroup_data->put("destroy", $formgroup->destroy);
                $individual_formgroup_data->put("created_at", $formgroup->created_at->toDateTimeString());
                $individual_formgroup_data->put("updated_at", $formgroup->updated_at->toDateTimeString());

                $all_formgroup_data->push($individual_formgroup_data);
                $count++;
            }

            DB::table("backup_partial_progress")->where("id", $row_id)->increment("progress", $count, ["updated_at" => Carbon::now()] );
            $increment = DB::table("backup_partial_progress")->where("id", $row_id)->pluck("progress");
            $this->backup_fs->put($table_path . $increment . ".json", json_encode($all_formgroup_data));
        });
        DB::table("backup_overall_progress")->where("id", $this->backup_id)->increment("progress",1,["updated_at"=>Carbon::now()]);
    }
}