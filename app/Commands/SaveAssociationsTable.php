<?php namespace App\Commands;

use Carbon\Carbon;
use App\RichTextField;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldBeQueued;

class SaveAssociationsTable extends Command implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /**
     * Execute the command.
     */
    public function handle() {
        Log::info("Started backing up the Associations table.");

        $table_path = $this->backup_filepath . "/associations/";
        $table_array = $this->makeBackupTableArray("associations");
        if($table_array == false) { return;}

        $row_id = DB::table('backup_partial_progress')->insertGetId(
            $table_array
        );

        $this->backup_fs->makeDirectory($table_path);
        DB::table('associations')->chunk(500, function($assocs) use ($table_path, $row_id) {
            $count = 0;
            $all_association_data = new Collection();

            foreach($assocs as $association) {
                $individual_association_data = new Collection();

                $individual_association_data->put("id", $association->id);
                $individual_association_data->put("dataForm", $association->dataForm);
                $individual_association_data->put("assocForm", $association->assocForm);
                $individual_association_data->put("created_at", $association->created_at); // Already a string, don't format.
                $individual_association_data->put("updated_at", $association->updated_at);

                $all_association_data->push($individual_association_data);
                $count++;
            }

            DB::table('backup_partial_progress')->where('id',$row_id)->increment('progress',$count,['updated_at'=>Carbon::now()]);
            $increment = DB::table('backup_partial_progress')->where('id',$row_id)->pluck('progress');
            $this->backup_fs->put($table_path . $increment . ".json",json_encode($all_association_data));
        });
        DB::table("backup_overall_progress")->where("id", $this->backup_id)->increment("progress",1,["updated_at"=>Carbon::now()]);
    }
}