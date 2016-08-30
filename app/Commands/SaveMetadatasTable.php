<?php namespace App\Commands;

use App\Metadata;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldBeQueued;

class SaveMetadatasTable extends Command implements SelfHandling, ShouldBeQueued
{
    use InteractsWithQueue, SerializesModels;

    /**
     * Execute the method.
     */
    public function handle() {
        Log::info("Started backing up the Metadatas table.");

        $table_path = $this->backup_filepath . "/metadatas/";

        $row_id = DB::table('backup_partial_progress')->insertGetId(
            $this->makeBackupTableArray("metadatas")
        );

        $this->backup_fs->makeDirectory($table_path);
        Metadata::chunk(1000, function($metadatas) use ($table_path, $row_id) {
            $count = 0;
            $all_metadatas_data = new Collection();

            foreach ($metadatas as $metadata) {
                $individual_metadata_data = new Collection();

                $individual_metadata_data->put("flid", $metadata->flid);
                $individual_metadata_data->put("pid", $metadata->pid);
                $individual_metadata_data->put("fid", $metadata->fid);
                $individual_metadata_data->put("name", $metadata->name);
                $individual_metadata_data->put("created_at", $metadata->created_at->toDateTimeString());
                $individual_metadata_data->put("updated_at", $metadata->updated_at->toDateTimeString());

                $all_metadatas_data->push($individual_metadata_data);
                $count++;
            }

            DB::table("backup_partial_progress")->where("id", $row_id)->increment("progress", $count, ["updated_at" => Carbon::now()] );
            $increment = DB::table("backup_partial_progress")->where("id", $row_id)->pluck("progress");
            $this->backup_fs->put($table_path . $increment . ".json", json_encode($all_metadatas_data));
        });
        DB::table("backup_overall_progress")->where("id", $this->backup_id)->increment("progress",1,["updated_at"=>Carbon::now()]);
    }
}