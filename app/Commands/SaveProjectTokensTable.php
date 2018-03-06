<?php namespace App\Commands;

use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;

class SaveProjectTokensTable extends Command implements ShouldQueue {

    /*
    |--------------------------------------------------------------------------
    | Save Project Tokens Table
    |--------------------------------------------------------------------------
    |
    | This command handles the backup of the project tokens table
    |
    */

    use InteractsWithQueue, SerializesModels;

    /**
     * Execute the command.
     */
    public function handle() {
        Log::info("Started backing up the Project Tokens table.");

        $table_path = $this->backup_filepath . "/project_token/";
        $table_array = $this->makeBackupTableArray("project_token");
        if($table_array == false) { return;}

        $row_id = DB::table('backup_partial_progress')->insertGetId(
            $table_array
        );

        $this->backup_fs->makeDirectory($table_path);
        DB::table('project_token')->orderBy('project_pid')->chunk(500, function($projToks) use ($table_path, $row_id) {
            $count = 0;
            $all_projecttoken_data = new Collection();

            foreach($projToks as $projtoken) {
                $individual_projecttoken_data = new Collection();

                $individual_projecttoken_data->put("project_pid", $projtoken->project_pid);
                $individual_projecttoken_data->put("token_id", $projtoken->token_id);

                $all_projecttoken_data->push($individual_projecttoken_data);
                $count++;
            }

            DB::table('backup_partial_progress')->where('id',$row_id)->increment('progress',$count,['updated_at'=>Carbon::now()]);
            $increment = DB::table('backup_partial_progress')->where('id',$row_id)->pluck('progress');
            $this->backup_fs->put($table_path . $increment . ".json",json_encode($all_projecttoken_data));
        });
        DB::table("backup_overall_progress")->where("id", $this->backup_id)->increment("progress",1,["updated_at"=>Carbon::now()]);
    }
}