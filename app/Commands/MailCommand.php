<?php namespace App\Commands;

use Illuminate\Bus\Queueable;

abstract class MailCommand {

    /*
    |--------------------------------------------------------------------------
    | Command
    |--------------------------------------------------------------------------
    |
    | This command handles the kora backup process
    |
    */

    use Queueable;

    /*************************************************************************************
     * Children must use InteractsWithQueue and SerializesModels from the Queue library. *
     *************************************************************************************/

    /**
     * @var string - Type of mail operation to perform
     */
    public $operation;

    /**
     * @var array - $optional variables to complete the mail request
     */
    public $options;

    /**
     * Constructs command and adds itself to the overall progress.
     *
     * @param $operation - Backup filesystem, disk instance
     * @param $options - Backup file path, the backup json will be output here
     */
    public function __construct($operation, $options) {
        $this->operation = $operation;
        $this->options = $options;
    }
}
