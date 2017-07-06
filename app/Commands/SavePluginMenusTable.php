<?php namespace App\Commands;

use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;

class SavePluginMenusTable extends Command implements SelfHandling, ShouldQueue {

    /*
    |--------------------------------------------------------------------------
    | Save Plugin Menus Table
    |--------------------------------------------------------------------------
    |
    | This command handles the backup of the plugin menus table
    |
    */

    use InteractsWithQueue, SerializesModels;

    /**
     * Execute the command.
     */
    public function handle() {
        Log::info("Started backing up the Plugin Menus table.");

        $table_path = $this->backup_filepath . "/plugin_menus/";
        $table_array = $this->makeBackupTableArray("plugin_menus");
        if($table_array == false) { return;}

        $row_id = DB::table('backup_partial_progress')->insertGetId(
            $table_array
        );

        $this->backup_fs->makeDirectory($table_path);
        DB::table('plugin_menus')->chunk(500, function($pluginMenus) use ($table_path, $row_id) {
            $count = 0;
            $all_pluginmenus_data = new Collection();

            foreach($pluginMenus as $pluginmenu) {
                $individual_pluginmenus_data = new Collection();

                $individual_pluginmenus_data->put("id", $pluginmenu->id);
                $individual_pluginmenus_data->put("plugin_id", $pluginmenu->plugin_id);
                $individual_pluginmenus_data->put("name", $pluginmenu->name);
                $individual_pluginmenus_data->put("url", $pluginmenu->url);
                $individual_pluginmenus_data->put("order", $pluginmenu->order);
                $individual_pluginmenus_data->put("created_at", $pluginmenu->created_at->toDateTimeString());
                $individual_pluginmenus_data->put("updated_at", $pluginmenu->updated_at->toDateTimeString());

                $all_pluginmenus_data->push($individual_pluginmenus_data);
                $count++;
            }

            DB::table('backup_partial_progress')->where('id',$row_id)->increment('progress',$count,['updated_at'=>Carbon::now()]);
            $increment = DB::table('backup_partial_progress')->where('id',$row_id)->pluck('progress');
            $this->backup_fs->put($table_path . $increment . ".json",json_encode($all_pluginmenus_data));
        });
        DB::table("backup_overall_progress")->where("id", $this->backup_id)->increment("progress",1,["updated_at"=>Carbon::now()]);
    }
}