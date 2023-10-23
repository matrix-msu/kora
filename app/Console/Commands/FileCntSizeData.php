<?php namespace App\Console\Commands;

use App\Http\Controllers\FormController;
use App\KoraFields\FileTypeField;
use App\Record;
use Illuminate\Console\Command;

class FileCntSizeData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kora:file-total-data {fid}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get the total file count and file size of the form';

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
        $fields = $form->layout['fields'];
        $recModel = new Record(array(),$form->id);

        $this->info('Beginning file data collection for ' . $form->internal_name . '...');
        $cnt = 0;
        $size = 0;
        foreach($fields as $flid => $field) {
            $fieldMod = $form->getFieldModel($field['type']);
            if($fieldMod instanceof FileTypeField) {
                //Get the record data
                $recData = $recModel->newQuery()->select(['id','kid',$flid])->whereNotNull($flid)->get();

                foreach($recData as $data) {

                    $fileArray = json_decode($data->{$flid},true);

                    foreach($fileArray as $file) {
                        $cnt++;
                        $size += $file['size'];
                    }
                }
            }
        }

        $size = fileSizeConvert($size);
        $this->info("File Cnt: $cnt | File Size: $size");
    }
}
