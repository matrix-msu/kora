<?php namespace App\Console\Commands;

use App\Form;
use App\Record;
use Illuminate\Console\Command;

class FixFileUrls extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kora3:file-url-fix';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fixes the public urls for files in the DB';

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
        $this->info('Rebuilding file field URLs...');

        $forms = Form::all();

        foreach($forms as $form) {
            $this->info('Processing Form '.$form->internal_name.'...');

            $fields = $form->layout['fields'];
            $recModel = new Record(array(),$form->id);

            foreach($fields as $flid => $field) {
                if(in_array($field['type'],[Form::_DOCUMENTS,Form::_GALLERY,Form::_PLAYLIST,Form::_VIDEO,Form::_3D_MODEL])) {
                    $fileData = $recModel->newQuery()->select('kid',$flid)->get();

                    foreach($fileData as $row) {
                        $dataURL = url('files').'/'. $row->kid.'/';

                        $updatedFiles = [];

                        $values = json_decode($row->{$flid},true);
                        if(is_null($values))
                            continue;

                        foreach($values as $val) {
                            $tmp = $val;
                            $tmp['url'] = $dataURL.urlencode($tmp['name']);
                            $updatedFiles[] = $tmp;
                        }

                        $newVal = json_encode($updatedFiles);

                        //update field
                        $recModel->newQuery()->where('kid','=',$row->kid)->update([$flid=>$newVal]);
                    }
                }
            }
        }

        $this->info('File URLs Rebuilt!');
    }
}
