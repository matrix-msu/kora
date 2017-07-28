<?php namespace App\Commands;

use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;

class SaveDashboardBlocksTable extends Command implements SelfHandling, ShouldQueue {

    /*
    |--------------------------------------------------------------------------
    | Save Dashboard Blocks Table
    |--------------------------------------------------------------------------
    |
    | This command handles the backup of the dashboard blocks table
    |
    */

    use InteractsWithQueue, SerializesModels;

    /**
     * Execute the command.
     */
    public function handle() {
        Log::info("Started backing up the Dashboard Blocks table.");

        $table_path = $this->backup_filepath . "/dashboard_blocks/";
        $table_array = $this->makeBackupTableArray("dashboard_blocks");
        if($table_array == false) { return;}

        $row_id = DB::table('backup_partial_progress')->insertGetId(
            $table_array
        );

        $this->backup_fs->makeDirectory($table_path);
        DB::table('dashboard_blocks')->chunk(500, function($dashblks) use ($table_path, $row_id) {
            $count = 0;
            $all_dashboardblock_data = new Collection();

            foreach($dashblks as $dashboardblock) {
                $individual_dashboardblock_data = new Collection();

                $individual_dashboardblock_data->put("id", $dashboardblock->id);
                $individual_dashboardblock_data->put("sec_id", $dashboardblock->sec_id);
                $individual_dashboardblock_data->put("type", $dashboardblock->type);
                $individual_dashboardblock_data->put("order", $dashboardblock->order);
                $individual_dashboardblock_data->put("options", $dashboardblock->options);
                $individual_dashboardblock_data->put("created_at", $dashboardblock->created_at->toDateTimeString());
                $individual_dashboardblock_data->put("updated_at", $dashboardblock->updated_at->toDateTimeString());

                $all_dashboardblock_data->push($individual_dashboardblock_data);
                $count++;
            }

            DB::table('backup_partial_progress')->where('id',$row_id)->increment('progress',$count,['updated_at'=>Carbon::now()]);
            $increment = DB::table('backup_partial_progress')->where('id',$row_id)->pluck('progress');
            $this->backup_fs->put($table_path . $increment . ".json",json_encode($all_dashboardblock_data));
        });
        DB::table("backup_overall_progress")->where("id", $this->backup_id)->increment("progress",1,["updated_at"=>Carbon::now()]);
    }
}