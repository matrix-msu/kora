<?php namespace App\Commands;

use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;

class SavePluginSettingsTable extends Command implements ShouldQueue {

    /*
    |--------------------------------------------------------------------------
    | Save Plugin Settings Table
    |--------------------------------------------------------------------------
    |
    | This command handles the backup of the plugin settings table
    |
    */

    use InteractsWithQueue, SerializesModels;

    /**
     * Execute the command.
     */
    public function handle() {
        Log::info("Started backing up the Plugin Settings table.");

        $table_path = $this->backup_filepath . "/plugin_settings/";
        $table_array = $this->makeBackupTableArray("plugin_settings");
        if($table_array == false) { return;}

        $row_id = DB::table('backup_partial_progress')->insertGetId(
            $table_array
        );

        $this->backup_fs->makeDirectory($table_path);
        DB::table('plugin_settings')->orderBy('id')->chunk(500, function($pluginSettings) use ($table_path, $row_id) {
            $count = 0;
            $all_pluginsettings_data = new Collection();

            foreach($pluginSettings as $pluginsetting) {
                $individual_pluginsettings_data = new Collection();

                $individual_pluginsettings_data->put("id", $pluginsetting->id);
                $individual_pluginsettings_data->put("plugin_id", $pluginsetting->plugin_id);
                $individual_pluginsettings_data->put("option", $pluginsetting->option);
                $individual_pluginsettings_data->put("value", $pluginsetting->value);
                $individual_pluginsettings_data->put("created_at", $pluginsetting->created_at); // Already a string, don't format.
                $individual_pluginsettings_data->put("updated_at", $pluginsetting->updated_at);

                $all_pluginsettings_data->push($individual_pluginsettings_data);
                $count++;
            }

            DB::table('backup_partial_progress')->where('id',$row_id)->increment('progress',$count,['updated_at'=>Carbon::now()]);
            $increment = DB::table('backup_partial_progress')->where('id',$row_id)->pluck('progress');
            $this->backup_fs->put($table_path . $increment . ".json",json_encode($all_pluginsettings_data));
        });
        DB::table("backup_overall_progress")->where("id", $this->backup_id)->increment("progress",1,["updated_at"=>Carbon::now()]);
    }
}