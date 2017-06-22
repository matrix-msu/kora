<?php

namespace App\Commands;

use App\AssociatorField;
use Carbon\Carbon;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SaveAssociatorSupportTable extends Command implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /**
     * Execute the command.
     */
    public function handle() {
        Log::info("Started backing up the Associator Support Table.");

        $table_path = $this->backup_filepath . "/associator_support/";
        $table_array = $this->makeBackupTableArray("associator_support");

        if ($table_array == false) { return; }

        $row_id = DB::table('backup_partial_progress')->insertGetId(
            $table_array
        );

        $this->backup_fs->makeDirectory($table_path);

        DB::table(AssociatorField::SUPPORT_NAME)->chunk(500, function($support_fields) use ($table_path, $row_id) {
            $count = 0;
            $all_support_data = new Collection();

            foreach ($support_fields as $support_field) {
                $individual_support_data = new Collection();

                $individual_support_data->put("id", $support_field->id);
                $individual_support_data->put("fid", $support_field->fid);
                $individual_support_data->put("rid", $support_field->rid);
                $individual_support_data->put("flid", $support_field->flid);
                $individual_support_data->put("record", $support_field->record);
                $individual_support_data->put("created_at", $support_field->created_at); // Already a string, don't format.
                $individual_support_data->put("updated_at", $support_field->updated_at);

                $all_support_data->push($individual_support_data);
                $count++;
            }

            DB::table('backup_partial_progress')->where('id',$row_id)->increment('progress', $count, ['updated_at'=> Carbon::now()]);
            $increment = DB::table('backup_partial_progress')->where('id',$row_id)->pluck('progress');
            $this->backup_fs->put($table_path . $increment . ".json", json_encode($all_support_data));
        });

        DB::table("backup_overall_progress")->where("id", $this->backup_id)->increment("progress", 1, ["updated_at"=>Carbon::now()]);
    }
}
