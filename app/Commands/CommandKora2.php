<?php namespace App\Commands;

use App\Http\Controllers\FormController;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;

abstract class CommandKora2 {

    use Queueable;

    /*************************************************************************************
     * Children must use InteractsWithQueue and SerializesModels from the Queue library. *
     *************************************************************************************/

    public $sid;  //original id of the scheme
    public $form;  // form that will be built
    public $pairArray;  //array of old to new pids
    public $dbInfo;  //info to connect to db
    public $filePath;  //local system path for kora 2 files
    public $exodus_id;  //progress table id

    /**
     * Command constructor.
     *
     * @param $sid
     * @param $fid
     * @param $pairArray
     * @param $dbInfo
     */
    public function __construct($sid, $fid, $pairArray, $dbInfo, $filePath, $exodus_id) {
        $this->sid = $sid;
        $this->form = FormController::getForm($fid);
        $this->pairArray = $pairArray;
        $this->dbInfo = $dbInfo;
        $this->filePath = $filePath;
        $this->exodus_id = $exodus_id;
        DB::table("exodus_overall_progress")->where("id", $this->exodus_id)->increment("overall", 1, ["updated_at" => Carbon::now()] );
    }

    /**
     * Makes an array for the backup_partial_progress table to insert.
     *
     * @param $name, name of the table to create the array for, e.g. text_fields.
     * @return array, the array to be inserted into the backup_partial_progress table.
     */
    public function makeBackupTableArray($recordCnt) {
        //need to make sure these tables are not running more than one
        $duplicate = DB::table('exodus_partial_progress')->where('name', $this->form->slug)->where('exodus_id', $this->exodus_id)->count();

        if($duplicate>0){
            return false;
        }

        return [
            "name" => $this->form->slug,
            "progress" => 0,
            "overall" => $recordCnt,
            "exodus_id" => $this->exodus_id,
            "start" => Carbon::now(),
            "created_at" => Carbon::now(),
            "updated_at" => Carbon::now()
        ];
    }
}
