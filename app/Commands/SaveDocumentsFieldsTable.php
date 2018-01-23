<?php namespace App\Commands;

use App\DocumentsField;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;

class SaveDocumentsFieldsTable extends Command implements ShouldQueue {

    /*
    |--------------------------------------------------------------------------
    | Save Documents Fields Table
    |--------------------------------------------------------------------------
    |
    | This command handles the backup of the documents fields table
    |
    */

    use InteractsWithQueue, SerializesModels;

    /**
     * Execute the command.
     */
    public function handle() {
        Log::info("Started backing up the Documents Fields table.");

        $table_path = $this->backup_filepath . "/documents_fields/";
        $table_array = $this->makeBackupTableArray("documents_fields");
        if($table_array == false) { return;}

        $row_id = DB::table('backup_partial_progress')->insertGetId(
            $table_array
        );

        $this->backup_fs->makeDirectory($table_path);
        DocumentsField::chunk(500, function($documents) use ($table_path, $row_id) {
            $count = 0;
            $all_documentsfields_data = new Collection();

            foreach($documents as $doc) {
                $individual_documentsfields_data = new Collection();

                $individual_documentsfields_data->put("id", $doc->id);
                $individual_documentsfields_data->put("rid", $doc->rid);
                $individual_documentsfields_data->put("fid", $doc->fid);
                $individual_documentsfields_data->put("flid", $doc->flid);
                $individual_documentsfields_data->put("documents", $doc->documents);
                $individual_documentsfields_data->put("created_at", $doc->created_at->toDateTimeString());
                $individual_documentsfields_data->put("updated_at", $doc->updated_at->toDateTimeString());

                $all_documentsfields_data->push($individual_documentsfields_data);
                $count++;
            }

            DB::table('backup_partial_progress')->where('id',$row_id)->increment('progress',$count,['updated_at'=>Carbon::now()]);
            $increment = DB::table('backup_partial_progress')->where('id',$row_id)->pluck('progress');
            $this->backup_fs->put($table_path . $increment . ".json",json_encode($all_documentsfields_data));
        });
        DB::table("backup_overall_progress")->where("id", $this->backup_id)->increment("progress",1,["updated_at"=>Carbon::now()]);
    }
}