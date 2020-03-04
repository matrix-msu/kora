<?php namespace App\Console\Commands;

use App\Revision;
use Illuminate\Console\Command;

class DisableRollbacks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kora:disable-rollbacks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Turns off rollback on all current revisions';

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
        $this->info('Disabling rollbacks...');

        Revision::where('rollback','=',1)->update(['rollback' => 0]);

        $this->info('Rollbacks disabled!');
    }
}
