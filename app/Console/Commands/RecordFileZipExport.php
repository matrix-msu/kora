<?php namespace App\Console\Commands;

use App\Http\Controllers\FormController;
use App\KoraFields\FileTypeField;
use App\Record;
use Illuminate\Console\Command;
use ZipArchive;

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

        $fileName = $this->buildExportFileZip($form);

        $this->info($fileName);
    }

    private function buildExportFileZip($form) {
        ini_set('max_execution_time',0);
        ini_set('memory_limit', "5000000000G");
        $fileCount = 0;

        //Build an array of the files that actually need to be zipped from every file field
        //This will ignore old record files
        //Also builds an array of local file names to original names to compensate for timestamps
        $recMod = new Record(array(), $form->id);
        $fileArray = [];
        foreach($form->layout['fields'] as $flid => $field) {
            if($form->getFieldModel($field['type']) instanceof FileTypeField) {
                $records = $recMod->newQuery()->select(['id','kid',$flid])->whereNotNull($flid)->get();
                foreach($records as $record) {
                    if(!is_null($record->{$flid})) {
                        $files = json_decode($record->{$flid}, true);
                        foreach($files as $recordFile) {
                            $fileCount++;

                            $localName = isset($recordFile['timestamp']) ? $recordFile['timestamp'] . '.' . $recordFile['name'] : $recordFile['name'];
                            $fileArray[$record->id][$localName] = $recordFile['name'];
                        }
                    }
                }
            }
        }

        if($fileCount == 0)
            return "No files found in the form: ".$form->name;

        switch(config('filesystems.kora_storage')) {
            case FileTypeField::_LaravelStorage:
                $zip_name = $form->internal_name.'preppedFile_'.uniqid().'.zip';
                $zip_dir = storage_path('app/tmpFiles');
                $zip = new ZipArchive();

                $dir_path = storage_path('app/files/'.$form->project_id . '/' . $form->id);
                if(
                    $zip->open($zip_dir . '/' . $zip_name, ZipArchive::CREATE) === TRUE ||
                    $zip->open($zip_dir . '/' . $zip_name, ZipArchive::OVERWRITE) === TRUE
                ) {
                    foreach($fileArray as $rid => $recordFileArray) {
                        foreach(new \DirectoryIterator("$dir_path/$rid") as $file) {
                            if($file->isFile() && array_key_exists($file->getFilename(), $recordFileArray)) {
                                $content = file_get_contents($file->getRealPath());
                                $zip->addFromString($rid.'/'.$recordFileArray[$file->getFilename()], $content);
                            }
                        }
                    }
                    $zip->close();
                }

                $filetopath = $zip_dir . '/' . $zip_name;

                if(file_exists($filetopath))
                    return "Success! File located at $filetopath";
                break;
            case FileTypeField::_JoyentManta:
                //TODO::MANTA
                break;
            default:
                break;
        }

        return "No files found in the form: ".$form->name;
    }
}
