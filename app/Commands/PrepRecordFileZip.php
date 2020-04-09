<?php namespace App\Commands;

use App\Form;
use App\KoraFields\FileTypeField;
use App\Record;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use ZipArchive;

class PrepRecordFileZip implements ShouldQueue {

    /*
    |--------------------------------------------------------------------------
    | Prep Record File Zip
    |--------------------------------------------------------------------------
    |
    | Builds a zip file of all the
    |
    */

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var int - ID for DB progress row
     */
    public $dbid;

    /**
     * @var string - Filename to download
     */
    public $filename;

    /**
     * @var Form - Form to zip
     */
    public $form;

    /**
     * @var mixed - KIDs of records to zip
     */
    public $kids;

    /**
     * Constructs command and adds itself to the overall progress.
     *
     * @param $recordForm - Form to zip
     * @param $kids - KIDs of records to zip
     */
    public function __construct($dbid, $filename, $form, $kids='ALL') {
        $this->dbid = $dbid;
        $this->filename = $filename;
        $this->form = $form;
        $this->kids = $kids;
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle() {
        ini_set('max_execution_time',0);
        ini_set('memory_limit', "51G");
        $query = DB::table('zip_progress')->where('id','=',$this->dbid);
        $totalFileSize = 0.0;
        $totalByteSize = 0;
        $fileCount = 0;

        //Build an array of the files that actually need to be zipped from every file field
        //This will ignore old record files
        //Also builds an array of local file names to original names to compensate for timestamps
        $recMod = new Record(array(), $this->form->id);
        $fileArray = [];
        foreach($this->form->layout['fields'] as $flid => $field) {
            if($this->form->getFieldModel($field['type']) instanceof FileTypeField) {
                $records = $recMod->newQuery()->select(['id','kid',$flid])->whereNotNull($flid)->get();
                foreach($records as $record) {
                    if(is_array($this->kids) && !in_array($record->kid,$this->kids))
                        continue;

                    if(!is_null($record->{$flid})) {
                        $files = json_decode($record->{$flid}, true);
                        foreach($files as $recordFile) {
                            $fileCount++;
                            $totalFileSize += number_format($recordFile['size'] / 1073741824, 2);
                            $totalByteSize += $recordFile['size'];
                            if($totalFileSize > 50) {
                                $query->update(["message" => "zip_too_big", "failed" => 1]);
                                return null;
                            }

                            $localName = isset($recordFile['timestamp']) ? $recordFile['timestamp'] . '.' . $recordFile['name'] : $recordFile['name'];
                            $fileArray[$record->id][$localName] = $recordFile['name'];
                        }
                    }
                }
            }
        }

        if($fileCount == 0) {
            $query->update(["message" => "no_record_files", "failed" => 1]);
            return null;
        }

        $query->update(["total_files" => $fileCount, "file_size" => fileSizeConvert($totalByteSize)]);

        switch(config('filesystems.kora_storage')) {
            case FileTypeField::_LaravelStorage:
                $zip_name = $this->filename;
                $zip_dir = storage_path('app/tmpFiles');
                $zip = new ZipArchive();

                $dir_path = storage_path('app/files/'.$this->form->project_id . '/' . $this->form->id);
                $count = 0;
                if(
                    $zip->open($zip_dir . '/' . $zip_name, ZipArchive::CREATE) === TRUE ||
                    $zip->open($zip_dir . '/' . $zip_name, ZipArchive::OVERWRITE) === TRUE
                ) {
                    foreach($fileArray as $rid => $recordFileArray) {
                        foreach(new \DirectoryIterator("$dir_path/$rid") as $file) {
                            if($file->isFile() && array_key_exists($file->getFilename(), $recordFileArray)) {
                                $content = file_get_contents($file->getRealPath());
                                $zip->addFromString($rid.'/'.$recordFileArray[$file->getFilename()], $content);
                                $zip->setCompressionIndex($count, ZipArchive::CM_STORE);
                                $count++;
                                $query->update(["files_finished" => $count]);
                            }
                        }
                    }
                    $zip->close();
                }

                $filetopath = $zip_dir . '/' . $zip_name;

                if(file_exists($filetopath)) {
                    $query->update(["message" => "success", "finished" => 1]);
                    return null;
                }
                break;
            case FileTypeField::_JoyentManta:
                //TODO::MANTA
                break;
            default:
                break;
        }

        $query->update(["message" => "no_record_files", "failed" => 1]);
    }
}
