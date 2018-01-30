<?php namespace App\Commands;

use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;

class SaveDashboardSectionsTable extends Command implements ShouldQueue {

    /*
    |--------------------------------------------------------------------------
    | Save Dashboard Sections Table
    |--------------------------------------------------------------------------
    |
    | This command handles the backup of the dashboard sections table
    |
    */

    use InteractsWithQueue, SerializesModels;

    /**
     * Execute the command.
     */
    public function handle() {
        Log::info("Started backing up the Dashboard Sections table.");

        $table_path = $this->backup_filepath . "/dashboard_sections/";
        $table_array = $this->makeBackupTableArray("dashboard_sections");
        if($table_array == false) { return;}

        $row_id = DB::table('backup_partial_progress')->insertGetId(
            $table_array
        );

        $this->backup_fs->makeDirectory($table_path);
        DB::table('dashboard_sections')->orderBy('id')->chunk(500, function($dashsecs) use ($table_path, $row_id) {
            $count = 0;
            $all_dashboardsection_data = new Collection();

            foreach($dashsecs as $dashboardsection) {
                $individual_dashboardsection_data = new Collection();

                $individual_dashboardsection_data->put("id", $dashboardsection->id);
                $individual_dashboardsection_data->put("uid", $dashboardsection->uid);
                $individual_dashboardsection_data->put("order", $dashboardsection->order);
                $individual_dashboardsection_data->put("title", $dashboardsection->title);
                $individual_dashboardsection_data->put("created_at", $dashboardsection->created_at->toDateTimeString());
                $individual_dashboardsection_data->put("updated_at", $dashboardsection->updated_at->toDateTimeString());

                $all_dashboardsection_data->push($individual_dashboardsection_data);
                $count++;
            }

            DB::table('backup_partial_progress')->where('id',$row_id)->increment('progress',$count,['updated_at'=>Carbon::now()]);
            $increment = DB::table('backup_partial_progress')->where('id',$row_id)->pluck('progress');
            $this->backup_fs->put($table_path . $increment . ".json",json_encode($all_dashboardsection_data));
        });
        DB::table("backup_overall_progress")->where("id", $this->backup_id)->increment("progress",1,["updated_at"=>Carbon::now()]);
    }
}