<?php namespace App\Console\Commands;

use App\Http\Controllers\FormController;
use Illuminate\Console\Command;

class RecordFileZip extends Command { //TODO::CASTLE

    /*
    |--------------------------------------------------------------------------
    | Record File Zip
    |--------------------------------------------------------------------------
    |
    | Generates a zip file of all record files for a particular form
    |
    */

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export:files {fid}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download Record files for a Form';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $fid = $this->argument('fid');

        $form = FormController::getForm($fid);

        $path = storage_path('app/files/p'.$form->pid.'/f'.$fid);
        $zipPath = storage_path('app/tmpFiles/'.$form->name.'_preppedZIP_CLI.zip');

        // Initialize archive object
        $zip = new \ZipArchive();
        $zip->open($zipPath, (\ZipArchive::CREATE | \ZipArchive::OVERWRITE));

        if(file_exists($path)) {
            ini_set('max_execution_time',0);

            //add files
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($path),
                \RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ($files as $name => $file) {
                // Skip directories (they would be added automatically)
                if (!$file->isDir()) {
                    // Get real and relative path for current file
                    $filePath = $file->getRealPath();
                    $relativePath = substr($filePath, strlen($path) + 1);

                    // Add current file to archive
                    $zip->addFile($filePath, $relativePath);
                }
            }
        } else {
            $this->info("No record files in form: $form->name");
;
            return '';
        }

        // Zip archive will be created only after closing object
        $zip->close();

        $this->info("Success! File located at storage/app/tmpFiles/".$form->name."_preppedZIP_CLI.zip");
    }
}
