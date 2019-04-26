<?php namespace App\Console\Commands;

use App\Http\Controllers\InstallController;
use App\Http\Controllers\UpdateController;
use App\Http\Requests\InstallRequest;
use Illuminate\Console\Command;

class UpdateKora extends Command
{
    /*
    |--------------------------------------------------------------------------
    | Finish Install Script
    |--------------------------------------------------------------------------
    |
    | This script finishes the Kora 3 update process by running any un-run update
    | scripts
    |
    */

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kora3:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Executes Kora 3 update scripts';

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

        $this->info("Kora 3 is up to date!");
    }
}
