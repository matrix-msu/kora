<?php namespace App\Commands;

use App\Http\Controllers\FormController;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;

abstract class CommandKora2 {

    /*
    |--------------------------------------------------------------------------
    | Command Kora 2
    |--------------------------------------------------------------------------
    |
    | This command handles the Kora3 exodus process
    |
    */

    use Queueable;

    /*************************************************************************************
     * Children must use InteractsWithQueue and SerializesModels from the Queue library. *
     *************************************************************************************/

    /**
     * @var int - Original id of the scheme
     */
    public $sid;
    /**
     * @var int - Form that will be built
     */
    public $form;
    /**
     * @var array - Array of old sids to new fids
     */
    public $formArray;
    /**
     * @var array - Array of old to new pids
     */
    public $pairArray;
    /**
     * @var array - Info to connect to db
     */
    public $dbInfo;
    /**
     * @var string - Local system path for kora 2 files
     */
    public $filePath;
    /**
     * @var int - Progress table id
     */
    public $exodus_id;

    /**
     * Constructs command and adds itself to the overall progress.
     *
     * @param $sid - Original id of the scheme
     * @param $fid - Form that will be built
     * @param $formArray - Array of old sids to new fids
     * @param $pairArray - Array of old to new pids
     * @param $dbInfo - Info to connect to db
     * @param $filePath - Local system path for kora 2 files
     * @param $exodus_id - Progress table id
     */
    public function __construct($sid, $fid, $formArray, $pairArray, $dbInfo, $filePath, $exodus_id) {
        $this->sid = $sid;
        $this->form = FormController::getForm($fid);
        $this->formArray = $formArray;
        $this->pairArray = $pairArray;
        $this->dbInfo = $dbInfo;
        $this->filePath = $filePath;
        $this->exodus_id = $exodus_id;
        DB::table("exodus_overall_progress")->where("id", $this->exodus_id)->increment("overall", 1, ["updated_at" => Carbon::now()] );
    }

    /**
     * Makes an array for the backup_partial_progress table to insert.
     *
     * @param $name - Name of the table to create the array for, e.g. text_fields
     * @return array - The array to be inserted into the backup_partial_progress table
     */
    public function makeBackupTableArray($recordCnt) {
        //need to make sure these tables are not running more than one
        $duplicate = DB::table('exodus_partial_progress')->where('name', $this->form->slug)->where('exodus_id', $this->exodus_id)->count();

        if($duplicate>0)
            return false;

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
