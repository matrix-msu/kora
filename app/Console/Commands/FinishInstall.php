<?php namespace App\Console\Commands;

use App\Http\Controllers\InstallController;
use App\Http\Requests\InstallRequest;
use Illuminate\Console\Command;

class FinishInstall extends Command
{
    /*
    |--------------------------------------------------------------------------
    | Finish Install Script
    |--------------------------------------------------------------------------
    |
    | THIS SCRIPT ALLOWS YOU TO FINISH THE KORA 3 INSTALLATION WITHOUT HAVING TO USE THE KORA INTERFACE
    |
    | db_host - Host name for the database
    | db_database - Name (schema) of database
    | db_username - User for accessing the database
    | db_password - Password for accessing the database
    | db_prefix - Prefix for database tables (i.e. 'kora3_')
    |
    | user_firstname - Admin user's first name
    | user_lastname - Admin user's last name
    | user_username - Admin's username
    | user_email - Admin's email
    | user_password - Admin's password
    | user_confirmpassword - Repeat password to confirm
    | user_organization - Organization that Admin belongs to
    | user_language - Admin's default language ('en' for English)
    |
    | mail_host - Host name for mail server
    | mail_from_address - Listed email for outgoing mail from Kora 3
    | mail_from_name - Listed name for outgoing mail from Kora 3
    | mail_username - Username for mail server
    | mail_password - Password for mail server
    |
    | recaptcha_public_key - Public key for ReCaptcha account
    | recaptcha_private_key - Private key for ReCaptcha account
    |
    */

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'install:finish {db_host} {db_database} {db_username} {db_password} {db_prefix} 
        {user_firstname} {user_lastname} {user_username} {user_email} {user_password} {user_confirmpassword} {user_organization} {user_language} 
        {mail_host} {mail_from_address} {mail_from_name} {mail_username} {mail_password} 
        {recaptcha_public_key} {recaptcha_private_key}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Executes final installation steps for Kora 3 from CLI';

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
        //Create a install request object !db_driver!
        $request = new InstallRequest();

        //Gather variables and store into it
        $request->db_host = $this->argument('db_host');
        $request->db_database = $this->argument('db_database');
        $request->db_username = $this->argument('db_username');
        $request->db_password = $this->argument('db_password');
        $request->db_prefix = $this->argument('db_prefix');

        $request->user_firstname = $this->argument('user_firstname');
        $request->user_lastname = $this->argument('user_lastname');
        $request->user_username = $this->argument('user_username');
        $request->user_email = $this->argument('user_email');
        $request->user_password = $this->argument('user_password');
        $request->user_confirmpassword = $this->argument('user_confirmpassword');
        $request->user_organization = $this->argument('user_organization');
        $request->user_language = $this->argument('user_language');

        $request->mail_host = $this->argument('mail_host');
        $request->mail_from_address = $this->argument('mail_from_address');
        $request->mail_from_name = $this->argument('mail_from_name');
        $request->mail_username = $this->argument('mail_username');
        $request->mail_password = $this->argument('mail_password');

        $request->recaptcha_public_key = $this->argument('recaptcha_public_key');
        $request->recaptcha_private_key = $this->argument('recaptcha_private_key');

        //Call Exodus function
        $ic = new InstallController();
        $ic->install($request);

        $ic->installPartTwo($request);
    }
}
