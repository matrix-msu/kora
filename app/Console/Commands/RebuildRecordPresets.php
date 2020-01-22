<?php namespace App\Console\Commands;

use App\Http\Controllers\RecordController;
use App\Http\Controllers\RecordPresetController;
use App\RecordPreset;
use Illuminate\Console\Command;

class RebuildRecordPresets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kora:record-preset-fix';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rebuilds any Record Presets that are based off existing records';

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
        $this->info('Rebuilding record presets...');

        $recPres = RecordPreset::all();
        $rpc = new RecordPresetController();

        foreach($recPres as $rp) {
            $record = RecordController::getRecord($rp->record_kid);

            if(is_null($record)) {
                $this->info("Record (".$rp->record_kid.") no longer exists. Cannot rebuild preset for this record.");
            } else {
                $name = $rp->preset['name'];
                $rp->preset = $rpc->getRecordArray($record,$name);
                $rp->save();
                $this->info("Record (".$rp->record_kid.") preset rebuilt!");
            }
        }

        $this->info('Record presets generated!');
    }
}
