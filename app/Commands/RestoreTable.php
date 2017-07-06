<?php namespace App\Commands;

use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;

class RestoreTable extends CommandRestore implements SelfHandling, ShouldQueue {

    /*
    |--------------------------------------------------------------------------
    | Restore Table
    |--------------------------------------------------------------------------
    |
    | This command handles restoration of backed up Kora3 tables
    |
    */

    use InteractsWithQueue, SerializesModels;

    /**
     * Execute the command.
     */
    public function handle() {
        $restore_path = $this->directory.'/'.$this->table;
        $table_array = $this->makeBackupTableArray();
        if($table_array == false) { return;}
        Log::info("Started restoring the ".$this->proper_name.".");

        $row_id = DB::table('restore_partial_progress')->insertGetId(
            $table_array
        );

        //We don't save the sysadmin row. If we ever needed to restore this row, we couldn't get to the backup page
        //DB::table('backup_partial_progress')->where('id',$row_id)->decrement("overall",1);
        Log::info('Iterating through data');
        foreach(new \DirectoryIterator($restore_path) as $file) {
            if($file->isFile()) {
                $jsondata = file_get_contents($restore_path.'/'.$file->getFilename());
                $data = json_decode($jsondata, true);
                foreach($data as $row) {
                    try {
                        DB::table($this->table)->insert($row);
                    } catch(\PDOException $e) {
                        Log::info("Restore error with Table ".$this->proper_name."\n".$e->getMessage());
                    }
                }
                DB::table("restore_partial_progress")->where("id", $row_id)->increment("progress", 1, ["updated_at" => Carbon::now()]);
            }
        }

        DB::table("restore_overall_progress")->where("id", $this->restore_id)->increment("progress",1,["updated_at"=>Carbon::now()]);
    }
}