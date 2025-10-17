<?php namespace App\Console\Commands;

use App\Http\Controllers\FormController;
use App\KoraFields\FileTypeField;
use App\Record;
use Illuminate\Console\Command;

class ExportMarkdown extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kora:export-markdown {fid} {uid} {title} {longform}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export records as ';

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
        ini_set('memory_limit','2G'); //We might be pulling a lot of rows so this is a safety precaution

        $fid = $this->argument('fid');
        $form = FormController::getForm($fid);

        //Get the data
        $filters = ["revAssoc" => true, "meta" => false, "fields" => 'ALL', "altNames" => false, "assoc" => false,
            "data" => true, "sort" => null, "count" => null, "index" => null];

        var_dump($form->getRecordsForExportMarkdown($filters, null, $this->argument('title'), $this->argument('longform')));
    }
}
