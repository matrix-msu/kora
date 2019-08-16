<?php namespace App\Console\Commands;

use App\Form;
use App\KoraFields\FileTypeField;
use App\Record;
use Illuminate\Console\Command;

class FileUrlFix extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kora:file-url-fix {domain}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the URLs for all files in the installation';

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
        $this->info('Beginning file URL fixes...');

        $forms = Form::all();
        $url = $this->argument('domain').'/files/'; //i.e. https://www.kora.com

        foreach($forms as $form) {
            $this->info('Processing fields for Form '.$form->internal_name.'...');

            $fields = $form->layout['fields'];
            $recModel = new Record(array(),$form->id);

            foreach($fields as $flid => $field) {
                $fieldMod = $form->getFieldModel($field['type']);
                if($fieldMod instanceof FileTypeField) {
                    //Update the record data
                    $recData = $recModel->newQuery()->select(['id','kid',$flid])->whereNotNull($flid)->get();

                    foreach($recData as $data) {
                        $dataURL = $url.$data->kid.'/';

                        $fileArray = json_decode($data->{$flid},true);
                        $newArray = [];

                        foreach($fileArray as $file) {
                            $file['url'] = $dataURL.urlencode($file['name']);
                            $newArray[] = $file;
                        }

                        //Update the change
                        $recModel->newQuery()->where('id','=',$data->id)->update([$flid => json_encode($newArray)]);
                    }
                }
            }
        }

        $this->info('URL fixes complete!');
    }
}
