<?php namespace App\Console\Commands;

use App\Commands\PrepRecordFileZip;
use App\Http\Controllers\FormController;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

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
        $dbid = DB::table('zip_progress')->insertGetId(['filename' => $filename]);

        PrepRecordFileZip::dispatch($dbid, $filename, $form, "ALL")->onQueue('zip_file');
        echo "Generating zip file...";

        $status = DB::table('zip_progress')->where('id','=',$dbid)->first();
        $timer = 0;
        while(!$status->finished && !$status->failed) {
            sleep(3);
            $timer++;
            $status = DB::table('zip_progress')->where('id','=',$dbid)->first();
            if($timer%3==0) //Helps give visual feedback to user
                echo '.';
        }
        echo "\n";

        if($status->finished)
            $this->info("Zip file successfully created at:".storage_path('app/tmpFiles/').$status->filename);
        else if($status->failed)
            $this->info("Zip file failed to create with error code: ".$status->message);
    }
}
