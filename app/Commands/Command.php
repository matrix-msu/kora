<?php namespace App\Commands;

abstract class Command {

    /*************************************************************************************
     * Children must use InteractsWithQueue and SerializesModels from the Queue library. *
     *************************************************************************************/

    protected $backup_fs;           ///< Backup filesystem, disk instance
    protected $backup_filepath;     ///< Backup file path, the backup json will be output here.
    protected $backup_id;           ///< Backup id, the job id stored in the database.

    /**
     * Command constructor.
     *
     * @param $backup_fs
     * @param $backup_filepath
     * @param $backup_id
     */
    public function __construct($backup_fs, $backup_filepath, $backup_id) {
        $this->backup_fs = $backup_fs;
        $this->backup_filepath = $backup_filepath;
        $this->backup_id = $backup_id;
    }
}
