<?php namespace App\Commands;


use App\OptionPreset;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldBeQueued;


class SaveOptionPresetsTable extends Command implements SelfHandling, ShouldBeQueued
{
    use InteractsWithQueue, SerializesModels;

    /**
     * Execute the command.
     */
    public function handle() {
        Log::info("Started backing up the Option Presets table.");

        $table_path = $this->backup_filepath . "/option_presets/";

        $row_id = DB::table('backup_partial_progress')->insertGetId(
            $this->makeBackupTableArray("option_presets")
        );

        $this->backup_fs->makeDirectory($table_path);
        OptionPreset::chunk(1000, function($optionpresets) use ($table_path, $row_id) {
            $count = 0;
            $all_optionpresets_data = new Collection();

            foreach ($optionpresets as $optionpreset) {
                $individual_optionpresets_data = new Collection();

                $individual_optionpresets_data->put("id", $optionpreset->id);
                $individual_optionpresets_data->put("pid", $optionpreset->pid);
                $individual_optionpresets_data->put("type", $optionpreset->type);
                $individual_optionpresets_data->put("name", $optionpreset->name);
                $individual_optionpresets_data->put("preset", $optionpreset->preset);
                $individual_optionpresets_data->put("shared", $optionpreset->shared);
                $individual_optionpresets_data->put("created_at", $optionpreset->created_at->toDateTimeString());
                $individual_optionpresets_data->put("updated_at", $optionpreset->updated_at->toDateTimeString());

                $all_optionpresets_data->push($individual_optionpresets_data);
                $count++;
            }

            DB::table("backup_partial_progress")->where("id", $row_id)->increment("progress", $count, ["updated_at" => Carbon::now()] );
            $increment = DB::table("backup_partial_progress")->where("id", $row_id)->pluck("progress");
            $this->backup_fs->put($table_path . $increment . ".json", json_encode($all_optionpresets_data));
           
        });
        DB::table("backup_overall_progress")->where("id", $this->backup_id)->increment("progress",1,["updated_at"=>Carbon::now()]);
    }
}