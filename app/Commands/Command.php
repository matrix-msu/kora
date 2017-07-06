<?php namespace App\Commands;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

abstract class Command {

    /*
    |--------------------------------------------------------------------------
    | Command
    |--------------------------------------------------------------------------
    |
    | This command handles the Kora3 backup process
    |
    */

    use Queueable;

    /*************************************************************************************
     * Children must use InteractsWithQueue and SerializesModels from the Queue library. *
     *************************************************************************************/

    /**
     * @var string - Backup filesystem, disk instance
     */
    public $backup_fs;
    /**
     * @var string - Backup file path, the backup json will be output here
     */
    public $backup_filepath;
    /**
     * @var int - Backup id, the job id stored in the database
     */
    public $backup_id;

    /**
     * Constructs command and adds itself to the overall progress.
     *
     * @param $backup_fs - Backup filesystem, disk instance
     * @param $backup_filepath - Backup file path, the backup json will be output here
     * @param $backup_id - Backup id, the job id stored in the database
     */
    public function __construct($backup_fs, $backup_filepath, $backup_id) {
        $this->backup_fs = Storage::disk($backup_fs);
        $this->backup_filepath = $backup_filepath;
        $this->backup_id = $backup_id;
        DB::table("backup_overall_progress")->where("id", $backup_id)->increment("overall", 1, ["updated_at" => Carbon::now()] );
    }

    /**
     * Makes an array for the backup_partial_progress table to insert.
     *
     * @param $name - Name of the table to create the array for, e.g. text_fields
     * @return array - The array to be inserted into the backup_partial_progress table
     */
    public function makeBackupTableArray($name) {
        $proper_name_pieces = explode("_", $name);
        $proper_name = "";
        foreach($proper_name_pieces as $piece) {
            $proper_name .= ucfirst($piece) . " ";
        }
        $proper_name .= "Table";

        //need to make sure these tables are not running more than one
        $duplicate = DB::table('backup_partial_progress')->where('name', $proper_name)->where('backup_id', $this->backup_id)->count();

        if($duplicate>0)
            return false;

        return [
            "name" => $proper_name,
            "progress" => 0,
            "overall" => DB::table($name)->count(),
            "backup_id" => $this->backup_id,
            "start" => Carbon::now(),
            "created_at" => Carbon::now(),
            "updated_at" => Carbon::now()
        ];
    }
}
