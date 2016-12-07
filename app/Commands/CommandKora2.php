<?php namespace App\Commands;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;

abstract class CommandKora2 {

    use Queueable;

    /*************************************************************************************
     * Children must use InteractsWithQueue and SerializesModels from the Queue library. *
     *************************************************************************************/

    public $sid;  //original id of the scheme
    public $fid;  // new id of the form
    public $projectArray;  //array of old to new pids
    public $dbInfo;  //info to connect to db

    /**
     * Command constructor.
     *
     * @param $sid
     * @param $fid
     * @param $pairArray
     * @param $dbInfo
     */
    public function __construct($sid, $fid, $pairArray, $dbInfo) {
        $this->sid = $sid;
        $this->fid = $fid;
        $this->pairArray = $pairArray;
        $this->dbInfo = $dbInfo;
        //DB::table("restore_overall_progress")->where("id", $this->restore_id)->increment("overall", 1, ["updated_at" => Carbon::now()] );
    }

    /**
     * Makes an array for the backup_partial_progress table to insert.
     *
     * @param $name, name of the table to create the array for, e.g. text_fields.
     * @return array, the array to be inserted into the backup_partial_progress table.
     */
    public function makeBackupTableArray() {
        //need to make sure these tables are not running more than one
        //$duplicate = DB::table('restore_partial_progress')->where('name', $this->proper_name)->where('restore_id', $this->restore_id)->count();

        /*if($duplicate>0){
            return false;
        }*/

        return [
            /*"name" => $this->proper_name,
            "progress" => 0,
            "overall" => count(glob($this->directory.'/'.$this->table.'/*.json')),
            "restore_id" => $this->restore_id,*/
            "start" => Carbon::now(),
            "created_at" => Carbon::now(),
            "updated_at" => Carbon::now()
        ];
    }
}
