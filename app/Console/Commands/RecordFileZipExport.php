<?php namespace App\Console\Commands;

use App\Http\Controllers\ExportController;
use App\Http\Controllers\FormController;
use Illuminate\Console\Command;
use Illuminate\Http\Request;

class RecordFileZipExport extends Command {

    /*
    |--------------------------------------------------------------------------
    | Record File Zip Export
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
    protected $signature = 'kora:record-file-zip {fid}';

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
        $filename = $form->internal_name.uniqid().'.zip';
        $exCtrl = new ExportController();

        $prep = $exCtrl->evaluateFormRecordsForExport($form,"ALL",$filename);
        $prep = $prep->original;

        if($prep['message']=='no_record_files')
            $this->error('No record files found in form: '.$form->name);
        else {
            $this->info("Generating zip file...");

            $request = new Request();
            $request['file_name'] = $filename;
            $request['file_array'] = $prep['file_array'];
            $exCtrl->buildFormRecordZip($form->project_id, $fid, $request);
            $expectedPath = storage_path('app/tmpFiles/').$filename;

            if(file_exists($expectedPath)) {
                $this->info("Zip file generated. You may retreive it at $expectedPath");
            } else {
                $this->error("Trouble finding generated zip file.");
            }
        }
    }
}
