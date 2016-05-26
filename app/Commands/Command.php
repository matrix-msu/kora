<?php namespace App\Commands;

use Illuminate\Support\Facades\Storage;

abstract class Command {

    /*************************************************************************************
     * Children must use InteractsWithQueue and SerializesModels from the Queue library. *
     *************************************************************************************/

    public $backup_fs;           ///< Backup filesystem, disk instance
    public $backup_filepath;     ///< Backup file path, the backup json will be output here.
    public $backup_id;           ///< Backup id, the job id stored in the database.

    /**
     * Command constructor.
     *
     * @param $backup_fs
     * @param $backup_filepath
     * @param $backup_id
     */
    public function __construct($backup_fs, $backup_filepath, $backup_id) {
        $this->backup_fs = Storage::disk($backup_fs);
        $this->backup_filepath = $backup_filepath;
        $this->backup_id = $backup_id;
    }
}
