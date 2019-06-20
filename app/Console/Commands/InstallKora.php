<?php namespace App\Console\Commands;

use App\Http\Controllers\InstallController;
use Illuminate\Console\Command;

class InstallKora extends Command
{
    /*
    |--------------------------------------------------------------------------
    | Finish Install Script
    |--------------------------------------------------------------------------
    |
    | This script finishes the kora install process by building the config
    | file, database, and default values
    |
    */

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kora:install';

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

        $password = uniqid();

        $result = $request->install($password);

        if($result) {
            $this->info("kora has finished initialization. Please review the following:");
            $this->info("Give READ access to the web user for your kora directory and ALL sub-folders");
            $this->info("Give WRITE access to the web user for the following directories and ALL their sub-folders");
            $this->info("    kora/bootstrap/cache/");
            $this->info("    kora/storage/");
            $this->info("    kora/public/assets/javascripts/production/");
            $this->info("Your password for user `admin` is $password");
        }
    }
}
