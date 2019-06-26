<?php namespace App\Console\Commands;

use App\Form;
use App\KoraFields\AssociatorField;
use App\Record;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AltNameFix extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kora:alt-fix';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fixes things missing alt_name';

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
        $this->info('Generating alt names...');

        $forms = Form::all();

        $this->info('Rebuilding fields...');
        foreach($forms as $form) {
            $this->info('Processing Form '.$form->internal_name.'...');

            $layout = $form->layout;
            $newFields = [];

            foreach($layout['fields'] as $flid => $field) {
                $newField = $field;
                if(!isset($field['alt_name'])) {
                    $newField['alt_name'] = '';
                }
                $newFields[$flid] = $newField;
            }

            $layout['fields'] = $newFields;
            $form->layout = $layout;
            $form->save();
        }

        $this->info('Alt names generated!');
    }
}
