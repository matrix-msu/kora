<?php namespace App\Commands;

use App\Revision;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;

class SaveRevisionsTable extends Command implements ShouldQueue {

    /*
    |--------------------------------------------------------------------------
    | Save Revisions Table
    |--------------------------------------------------------------------------
    |
    | This command handles the backup of the revisions table
    |
    */

    use InteractsWithQueue, SerializesModels;

    /**
     * Execute the command.
     */
    public function handle() {
        Log::info("Started backing up the Revisions table.");

        $table_path = $this->backup_filepath . "/revisions/";
        $table_array = $this->makeBackupTableArray("revisions");
        if($table_array == false) { return;}

        $row_id = DB::table('backup_partial_progress')->insertGetId(
            $table_array
        );

        $this->backup_fs->makeDirectory($table_path);
        Revision::chunk(500, function($revisions) use ($table_path, $row_id) {
            $count = 0;
            $all_revisions_data = new Collection();

            foreach($revisions as $revision) {
                $individual_revision_data = new Collection();

                $individual_revision_data->put("id", $revision->id);
                $individual_revision_data->put("fid", $revision->fid);
                $individual_revision_data->put("rid", $revision->rid);
                $individual_revision_data->put("owner", $revision->owner);
                $individual_revision_data->put("type", $revision->type);
                $individual_revision_data->put("data", $revision->data);
                $individual_revision_data->put("oldData", $revision->oldData);
                $individual_revision_data->put("rollback", $revision->rollback);
                $individual_revision_data->put("created_at", $revision->created_at->toDateTimeString());
                $individual_revision_data->put("updated_at", $revision->updated_at->toDateTimeString());

                $all_revisions_data->push($individual_revision_data);
                $count++;
            }

            DB::table("backup_partial_progress")->where("id", $row_id)->increment("progress", $count, ["updated_at" => Carbon::now()] );
            $increment = DB::table("backup_partial_progress")->where("id", $row_id)->pluck("progress");
            $this->backup_fs->put($table_path . $increment . ".json", json_encode($all_revisions_data));
        });
        DB::table("backup_overall_progress")->where("id", $this->backup_id)->increment("progress",1,["updated_at"=>Carbon::now()]);
    }
}