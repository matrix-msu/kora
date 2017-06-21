<?php namespace App\Commands;

use App\Page;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldBeQueued;

class SavePagesTable extends Command implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /**
     * Execute the command.
     */
    public function handle() {
        Log::info("Started backing up the Pages table.");

        $table_path = $this->backup_filepath . "/pages/";
        $table_array = $this->makeBackupTableArray("pages");
        if($table_array == false) { return;}

        $row_id = DB::table('backup_partial_progress')->insertGetId(
            $table_array
        );

        $this->backup_fs->makeDirectory($table_path);
        Page::chunk(500, function($pages) use ($table_path, $row_id) {
            $count = 0;
            $all_pages_data = new Collection();

            foreach ($pages as $page) {
                $page_data = new Collection();

                $page_data->put("id", $page->id);
                $page_data->put("parent_type", $page->parent_type);
                $page_data->put("fid", $page->fid);
                $page_data->put("title", $page->title);
                $page_data->put("sequence", $page->sequence);
                $page_data->put("created_at", $page->created_at->toDateTimeString());
                $page_data->put("updated_at", $page->updated_at->toDateTimeString());

                $all_pages_data->push($page_data);
                $count++;
            }

            DB::table("backup_partial_progress")->where("id", $row_id)->increment("progress", $count, ["updated_at" => Carbon::now()] );
            $increment = DB::table("backup_partial_progress")->where("id", $row_id)->pluck("progress");
            $this->backup_fs->put($table_path . $increment . ".json", json_encode($all_pages_data));
        });
        DB::table("backup_overall_progress")->where("id", $this->backup_id)->increment("progress",1,["updated_at"=>Carbon::now()]);
    }
}