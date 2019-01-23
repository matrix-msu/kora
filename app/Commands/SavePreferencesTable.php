<?php namespace App\Commands;

use App\Preference;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;

class SavePreferencesTable extends Command implements ShouldQueue {

    /*
    |--------------------------------------------------------------------------
    | Save Preferences Table
    |--------------------------------------------------------------------------
    |
    | This command handles the backup of the preferences table
    |
    */

    use InteractsWithQueue, SerializesModels;

    /**
     * Execute the command.
     */
    public function handle() {
        Log::info("Started backing up the Preferences table.");

        $table_path = $this->backup_filepath . "/preferences/";
        $table_array = $this->makeBackupTableArray("preferences");
        if($table_array == false) { return;}

        $row_id = DB::table('backup_partial_progress')->insertGetId(
            $table_array
        );

        //We don't save the sysadmin row. If we ever needed to restore this row, we couldn't get to the backup page
        DB::table('backup_partial_progress')->where('id',$row_id)->decrement("overall",1);

        $this->backup_fs->makeDirectory($table_path);
        Preference::chunk(500, function($prefs) use ($table_path, $row_id) {
            $count = 0;
            $all_prefs_data = new Collection();

            foreach($prefs as $pref) {
                $individual_pref_data = new Collection();

                $individual_pref_data->put("id", $pref->id);
                $individual_pref_data->put("user_id", $pref->user_id);
                $individual_pref_data->put("use_dashboard", $pref->use_dashboard);
                $individual_pref_data->put("logo_target", $pref->logo_target);
                $individual_pref_data->put("proj_page_tab_selection", $pref->proj_page_tab_selection);
                $individual_pref_data->put("single_proj_page_tab_selection", $pref->single_proj_page_tab_selection);
                $individual_pref_data->put("onboarding", $pref->onboarding);
                $individual_pref_data->put("created_at", $pref->created_at->toDateTimeString());
                $individual_pref_data->put("updated_at", $pref->updated_at->toDateTimeString());

                $all_prefs_data->push($individual_pref_data);
                $count++;
            }

            DB::table("backup_partial_progress")->where("id", $row_id)->increment("progress", $count, ["updated_at" => Carbon::now()] );
            $increment = DB::table("backup_partial_progress")->where("id", $row_id)->pluck("progress");
            $this->backup_fs->put($table_path . $increment . ".json", json_encode($all_prefs_data));
        });
        DB::table("backup_overall_progress")->where("id", $this->backup_id)->increment("progress",1,["updated_at"=>Carbon::now()]);
    }
}