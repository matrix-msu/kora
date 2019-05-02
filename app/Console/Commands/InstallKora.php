<?php namespace App\Console\Commands;

use App\Http\Controllers\InstallController;
use App\Http\Controllers\UpdateController;
use App\Http\Requests\InstallRequest;
use Illuminate\Console\Command;

class InstallKora extends Command
{
    /*
    |--------------------------------------------------------------------------
    | Finish Install Script
    |--------------------------------------------------------------------------
    |
    | This script finishes the Kora 3 install process by building the config
    | file, database, and default values
    |
    */

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kora3:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Finishes the setup for your Kora installation';

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
        $request = new InstallController();

        $this->info("Beginning Installation Process...");

        $result = $request->install();

        if($result) {
            $this->info("Installation complete! Please make sure all files are readable by the web user.");
            $this->info("The web user also needs write permissions to the following directories and their subdirectories:");
            $this->info("Kora3/bootstrap/cache/");
            $this->info("Kora3/storage/");
            $this->info("Kora3/public/assets/javascripts/production/");
        }
    }
}
