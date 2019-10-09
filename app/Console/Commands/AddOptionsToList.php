<?php namespace App\Console\Commands;

use App\Http\Controllers\FormController;
use Illuminate\Console\Command;

class AddOptionsToList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kora:add-to-list {fid} {field_id} {overwrite} {options}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update a fields list options';

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
        $this->info('Beginning list additions...');

        $fid = $this->argument('fid');
        $flid = $this->argument('field_id');
        $overwrite = $this->argument('overwrite');
        $options = json_decode($this->argument('options'),true);

        $form = FormController::getForm($fid);
        $layout = $form->layout;

        if(!$overwrite) {
            $oldOpts = $layout['fields'][$flid]['options']['Options'];
            $options = array_merge($options, $oldOpts);
            $options = array_unique($options);
        }

        $layout['fields'][$flid]['options']['Options'] = $options;
        $form->layout = $layout;
        $form->save();

        if($layout['fields'][$flid]['type'] == 'List') {
            $table = new \CreateRecordsTable();
            $table->updateEnum($fid,$flid,$options);
        }

        $this->info('List additions complete!');
    }
}
