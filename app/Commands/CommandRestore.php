<?php namespace App\Commands;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;

abstract class CommandRestore {

    use Queueable;

    /*************************************************************************************
     * Children must use InteractsWithQueue and SerializesModels from the Queue library. *
     *************************************************************************************/

    public $directory;                  ///< Path where the restore files exist
    public $restore_id;                 ///< Restore id, the job id stored in the database.
    public $table;                      ///< String of table name
    public $proper_name = "";           ///< Readable name of table

    /**
     * Command constructor.
     *
     * @param $backup_fs
     * @param $backup_filepath
     * @param $backup_id
     */
    public function __construct($table, $dir, $restore_id) {
        $this->table = $table;
        $this->directory = $dir;
        $this->restore_id = $restore_id;
        DB::table("restore_overall_progress")->where("id", $this->restore_id)->increment("overall", 1, ["updated_at" => Carbon::now()] );
    }

    /**
     * Makes an array for the backup_partial_progress table to insert.
     *
     * @param $name, name of the table to create the array for, e.g. text_fields.
     * @return array, the array to be inserted into the backup_partial_progress table.
     */
    public function makeBackupTableArray() {
        $proper_name_pieces = explode("_", $this->table);
        foreach ($proper_name_pieces as $piece) {
            $this->proper_name .= ucfirst($piece) . " ";
        }
        $this->proper_name .= "Table";

        //need to make sure these tables are not running more than one
        $duplicate = DB::table('restore_partial_progress')->where('name', $this->proper_name)->where('restore_id', $this->restore_id)->count();

        if($duplicate>0){
            return false;
        }

        return [
            "name" => $this->proper_name,
            "progress" => 0,
            "overall" => count(glob($this->directory.'/'.$this->table.'/*.json')),
            "restore_id" => $this->restore_id,
            "start" => Carbon::now(),
            "created_at" => Carbon::now(),
            "updated_at" => Carbon::now()
        ];
    }
}
