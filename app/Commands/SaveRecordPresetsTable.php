<?php namespace App\Commands;

use App\RecordPreset;
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

class SaveRecordPresetsTable extends Command implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /**
     * Execute the command.
     */
    public function handle() {
        Log::info("Started backing up the Record Presets table.");

        $table_path = $this->backup_filepath . "/record_presets/";
        $table_array = $this->makeBackupTableArray("record_presets");
        if($table_array == false) { return;}

        $row_id = DB::table('backup_partial_progress')->insertGetId(
            $table_array
        );

        $this->backup_fs->makeDirectory($table_path);
        RecordPreset::chunk(500, function($recPresets) use ($table_path, $row_id) {
            $count = 0;
            $all_recordpresets_data = new Collection();

            foreach($recPresets as $recordpreset) {
                $individual_recordpresets_data = new Collection();

                $individual_recordpresets_data->put("id", $recordpreset->id);
                $individual_recordpresets_data->put("fid", $recordpreset->fid);
                $individual_recordpresets_data->put("rid", $recordpreset->rid);
                $individual_recordpresets_data->put("name", $recordpreset->name);
                $individual_recordpresets_data->put("preset", $recordpreset->preset);
                $individual_recordpresets_data->put("created_at", $recordpreset->created_at->toDateTimeString());
                $individual_recordpresets_data->put("updated_at", $recordpreset->updated_at->toDateTimeString());

                $all_recordpresets_data->push($individual_recordpresets_data);
                $count++;
            }

            DB::table('backup_partial_progress')->where('id',$row_id)->increment('progress',$count,['updated_at'=>Carbon::now()]);
            $increment = DB::table('backup_partial_progress')->where('id',$row_id)->pluck('progress');
            $this->backup_fs->put($table_path . $increment . ".json",json_encode($all_recordpresets_data));
        });
        DB::table("backup_overall_progress")->where("id", $this->backup_id)->increment("progress",1,["updated_at"=>Carbon::now()]);
    }
}