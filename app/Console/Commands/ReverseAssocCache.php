<?php namespace App\Console\Commands;

use App\Form;
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
        $this->info('Generating reverse association cache...');

        $forms = Form::all();
        $inserts = [];

        $this->info('Clearing old cache...');
        DB::table('reverse_associator_cache')->truncate();

        $this->info('Rebuilding cache...');
        foreach($forms as $form) {
            $this->info('Processing Form '.$form->internal_name.'...');

            $fields = $form->layout['fields'];
            $recModel = new Record(array(),$form->id);

            foreach($fields as $flid => $field) {
                if($field['type'] == 'Associator') {
                    $assocData = $recModel->newQuery()->select('kid',$flid)->get();
                    foreach($assocData as $row) {
                        $values = json_decode($row->{$flid},true);
                        foreach($values as $val) {
                            $inserts[] = [
                                'associated_kid' => $val,
                                'source_kid' => $row->kid,
                                'source_flid' => $flid,
                                'source_form_id' => $form->id
                            ];
                        }
                    }
                }
            }
        }

        $this->info('Storing values...');
        DB::table('reverse_associator_cache')->insert($inserts);

        $this->info('Reverse association cache generated!');
    }
}
