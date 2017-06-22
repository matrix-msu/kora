<?php

namespace App\Commands;

use App\AssociatorField;
use App\Commands\Command;
use Carbon\Carbon;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SaveAssociatorFieldsTable extends Command implements SelfHandling
{
    use InteractsWithQueue, SerializesModels;

    /**
     * Execute the command.
     */
    public function handle() {
        Log::info("Started backing up the Associator Fields table.");

        $table_path = $this->backup_filepath . "/associator_fields/";
        $table_array = $this->makeBackupTableArray("associator_fields");
        if($table_array == false) { return;}

        $row_id = DB::table('backup_partial_progress')->insertGetId(
            $table_array
        );

        $this->backup_fs->makeDirectory($table_path);
        AssociatorField::chunk(500, function($assocfields) use ($table_path, $row_id) {
            $count = 0;
            $all_associatorfields_data = new Collection();

            foreach($assocfields as $associatorfield) {
                $individual_associatorfields_data = new Collection();

                $individual_associatorfields_data->put("id", $associatorfield->id);
                $individual_associatorfields_data->put("rid", $associatorfield->rid);
                $individual_associatorfields_data->put("flid", $associatorfield->flid);
                $individual_associatorfields_data->put("fid", $associatorfield->fid);
                $individual_associatorfields_data->put("created_at", $associatorfield->created_at->toDateTimeString());
                $individual_associatorfields_data->put("updated_at", $associatorfield->updated_at->toDateTimeString());

                $all_associatorfields_data->push($individual_associatorfields_data);
                $count++;
            }

            DB::table('backup_partial_progress')->where('id',$row_id)->increment('progress',$count,['updated_at'=>Carbon::now()]);
            $increment = DB::table('backup_partial_progress')->where('id',$row_id)->pluck('progress');
            $this->backup_fs->put($table_path . $increment . ".json",json_encode($all_associatorfields_data));
        });
        DB::table("backup_overall_progress")->where("id", $this->backup_id)->increment("progress",1,["updated_at"=>Carbon::now()]);
    }
}
