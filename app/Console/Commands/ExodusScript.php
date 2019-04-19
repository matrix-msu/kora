<?php namespace App\Console\Commands;

use App\Http\Controllers\ExodusController;
use Illuminate\Console\Command;
use Illuminate\Http\Request;

class ExodusScript extends Command {

    /*
    |--------------------------------------------------------------------------
    | Exodus Script
    |--------------------------------------------------------------------------
    |
    | 1) THIS SCRIPT ALLOWS YOU TO RUN PROJECT MIGRATION FROM THE COMMAND LINE
    |
    | 2) MAKE SURE YOUR COMMAND LINE USER HAS FULL ACCESS TO ALL FILES AND FOLDERS IN KORA
    |
    | 3) THIS SCRIPT DOES NOT SUPPORT USER AND TOKEN MIGRATION
    |
    | 4) IT PRIMARILY IS USED FOR LARGER PROJECTS THAT THE WEB MIGRATION CANT HANDLE
    |
    | 5) THIS SCRIPT DOES NOT LOCK USERS OUT SO BE AWARE OF THAT
    |
    */

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exodus:migrate {dbhost} {dbuser} {dbname} {dbpass} {project} {fileDir}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Executes exodus project migration from CLI';

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
        //Create a request object
        $request = new Request();

        //Gather variables and store into it
        $request->host = $this->argument('dbhost');
        $request->user = $this->argument('dbuser');
        $request->name = $this->argument('dbname');
        $request->pass = $this->argument('dbpass');
        $request->migrateUsers = false;
        $request->migrateTokens = false;
        $request->projects = $this->argument('project');
        $request->filePath = $this->argument('fileDir');

        //Call Exodus function
        $ec = new ExodusController();
        $ec->startExodus($request);

        $ec->finishExodus();
    }
}
