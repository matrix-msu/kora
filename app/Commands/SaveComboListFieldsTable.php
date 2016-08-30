<?php namespace App\Commands;

use Carbon\Carbon;
use App\ComboListField;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldBeQueued;

class SaveComboListFieldsTable extends Command implements SelfHandling, ShouldBeQueued
{
    use InteractsWithQueue, SerializesModels;

    /**
     * Execute the command.
     */
    public function handle() {
        Log::info("Started backing up the Combo List Fields table.");

        $table_path = $this->backup_filepath . "/combo_list_fields/";

        $row_id = DB::table('backup_partial_progress')->insertGetId(
            $this->makeBackupTableArray("combo_list_fields")
        );

        $this->backup_fs->makeDirectory($table_path);
        ComboListField::chunk(1000, function($combolistfields) use ($table_path, $row_id) {
            $count = 0;
            $all_combolistfield_data = new Collection();

            foreach ($combolistfields as $combolistfield) {
                $individual_combolistfield_data = new Collection();

                $individual_combolistfield_data->put('id',$combolistfield->id);
                $individual_combolistfield_data->put('rid',$combolistfield->rid);
                $individual_combolistfield_data->put('flid',$combolistfield->flid);
                $individual_combolistfield_data->put('options',$combolistfield->options);
                $individual_combolistfield_data->put('ftype1',$combolistfield->ftype1);
                $individual_combolistfield_data->put('ftype2',$combolistfield->ftype2);
                $individual_combolistfield_data->put("created_at", $combolistfield->created_at->toDateTimeString());
                $individual_combolistfield_data->put("updated_at", $combolistfield->updated_at->toDateTimeString());

                $all_combolistfield_data->push($individual_combolistfield_data);
                $count++;
            }

            DB::table("backup_partial_progress")->where("id", $row_id)->increment("progress", $count, ["updated_at" => Carbon::now()] );
            $increment = DB::table("backup_partial_progress")->where("id", $row_id)->pluck("progress");
            $this->backup_fs->put($table_path . $increment . ".json", json_encode($all_combolistfield_data));
        });
        DB::table("backup_overall_progress")->where("id", $this->backup_id)->increment("progress",1,["updated_at"=>Carbon::now()]);
    }
}