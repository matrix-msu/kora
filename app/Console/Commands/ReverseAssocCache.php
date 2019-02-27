<?php namespace App\Console\Commands;

use App\Form;
use App\KoraFields\AssociatorField;
use App\Record;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ReverseAssocCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'revAssoc:generate-cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Builds out the cache for records to reference any records that point at them';

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
        $this->info('Generating reverse association cache...');

        $forms = Form::all();

        $this->info('Clearing old cache...');
        DB::table(AssociatorField::Reverse_Cache_Table)->truncate();

        $this->info('Rebuilding cache...');
        foreach($forms as $form) {
            $this->info('Processing Form '.$form->internal_name.'...');

            $fields = $form->layout['fields'];
            $recModel = new Record(array(),$form->id);
            $first_message = true;

            foreach($fields as $flid => $field) {
                if($field['type'] == 'Associator') {
                    $assocData = $recModel->newQuery()->select('kid',$flid)->get();
                    foreach($assocData as $row) {
                        $inserts = [];

                        $values = json_decode($row->{$flid},true);
                        if(is_null($values))
                            continue;

                        foreach($values as $val) {
                            $inserts[] = [
                                'associated_kid' => $val,
                                'associated_form_id' => explode('-',$val)[1],
                                'source_kid' => $row->kid,
                                'source_flid' => $flid,
                                'source_form_id' => $form->id
                            ];
                        }

                        if(!empty($inserts)) {
                            //Break up the inserts into chuncks
                            if($first_message)
                                $this->info('Storing values for Form ' . $form->internal_name . '...');
                            $first_message = false;
                            DB::table(AssociatorField::Reverse_Cache_Table)->insert($inserts);
                        }
                    }
                }
            }
        }

        $this->info('Reverse association cache generated!');
    }
}
