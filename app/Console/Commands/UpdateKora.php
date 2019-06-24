<?php namespace App\Console\Commands;

use App\Http\Controllers\UpdateController;
use Illuminate\Console\Command;

class UpdateKora extends Command
{
    /*
    |--------------------------------------------------------------------------
    | Finish Update Script
    |--------------------------------------------------------------------------
    |
    | This script finishes the kora update process by running any un-run update
    | scripts
    |
    */

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kora:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Executes kora update scripts';

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
        $request = new UpdateController();

        $this->info("Running update scripts...");

        $request->runScripts();

        $this->info("kora is up to date!");
    }
}
