<?php namespace App\Http\Controllers;

use App\Metadata;
use Illuminate\Support\Collection;
use \Illuminate\Support\Facades\App;
Use \Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Schema;
use \Illuminate\Support\Facades\Session;
use \Illuminate\Support\Facades\Config;
use \Illuminate\Support\Facades\Artisan;
use PhpSpec\Exception\Exception;

class InstallController extends Controller {

	/*
	|--------------------------------------------------------------------------
	| Install Controller
	|--------------------------------------------------------------------------
	|
	| This controller handles generating the .env file and running the artisan
	| migration so the rest of the controllers can function.  It also creates the
	| first user.  And sets the application key, and creates needed folders.
	*/

    //Any directory in this array will be created for you during install with 0644 permission
    private $DIRECTORIES = ["storage/app/backups","storage/app/backups/user_upload","storage/app/tmpFiles","storage/app/files"];

	public function index(Request $request)
	{

		if(file_exists("../.env")){
			return redirect('/');
		}
		$not_installed = true;
        $languages_available = Config::get('app.locales_supported');

		return view('install.install',compact('languages_available','not_installed'));
	}

	public function install(\Illuminate\Http\Request $request){
		if(file_exists("../.env")){
			flash()->overlay(".env file already exists, can't overwrite","Whoops!");
			return redirect('/');
		}

		$envstrings = new Collection();
		$adminuser = new Collection();

		//Make sure all necessary values were submitted
		$this->validate($request,[
			'db_driver'=>'required|in:mysql,pgsql,sqlsrv,sqlite',
			'db_host'=>'required_if:db_driver,mysql,pgsql,sqlsrv',
			'db_database'=>'required_if:db_driver,mysql,pgsql,sqlsrv|alpha_dash',
			'db_username'=>'required_if:db_driver,mysql,pgsql,sqlsrv',
			'db_password'=>'required_if:db_driver,mysql,pgsql,sqlsrv',
			'db_prefix'=>'required|alpha_dash',
			'user_username'=>'required|alpha_dash',
			'user_email'=>'required|email',
			'user_password'=>'required|same:user_confirmpassword',
			'user_confirmpassword'=>'required',
			'user_realname'=>'required',
			'user_language'=>'required',
			'mail_host'=>'required',
			'mail_from_address'=>'required|email',
			'mail_from_name'=>'required',
			'mail_username'=>'required',
			'mail_password'=>'required',
			'recaptcha_public_key'=>'required',
			'recaptcha_private_key'=>'required',
			'baseurl_url'=>'required',
            'basepath'=>'required'
		]);

		$envstrings->put("db_driver",$request->input("db_driver"));
		$envstrings->put("db_host",$request->input("db_host"));
		$envstrings->put("db_database",$request->input("db_database"));
		$envstrings->put("db_username",$request->input("db_username"));
		$envstrings->put("db_password",$request->input("db_password"));
		$envstrings->put("db_prefix",$request->input("db_prefix"));
		$envstrings->put("mail_host",$request->input("mail_host"));
		$envstrings->put("mail_from_address",$request->input("mail_from_address"));
		$envstrings->put("mail_from_name",$request->input("mail_from_name"));
		$envstrings->put("mail_username",$request->input("mail_username"));
		$envstrings->put("mail_password",$request->input("mail_password"));
		$envstrings->put("recaptcha_public_key",$request->input("recaptcha_public_key"));
		$envstrings->put("recaptcha_private_key",$request->input("recaptcha_private_key"));
		$envstrings->put("baseurl_url",$request->input("baseurl_url"));
        $envstrings->put("basepath",$request->input("basepath"));

		$adminuser->put('user_username',$request->input("user_username"));
		$adminuser->put('user_email',$request->input("user_email"));
		$adminuser->put('user_password',$request->input('user_password'));
		$adminuser->put('user_realname',$request->input('user_realname'));
		$adminuser->put('user_language',$request->input('user_language'));


		try{
			$dbtype = $envstrings->get('db_driver');
			if($dbtype == "mysql"){
				$dbc = new \PDO('mysql:host='.$envstrings->get("db_host").';dbname='.$envstrings->get("db_database"),$envstrings->get('db_username'),$envstrings->get('db_password'));
			}
			elseif($dbtype == "pgsql") {
				$dbc = new \PDO('pgsql:host='.$envstrings->get("db_host").';dbname='.$envstrings->get("db_database"),$envstrings->get('db_username'),$envstrings->get('db_password'));
			}
			elseif($dbtype == "sqlsrv"){
				$dbc = new \PDO('pgsql:Server='.$envstrings->get("db_host").';Databasee='.$envstrings->get("db_database"),$envstrings->get('db_username'),$envstrings->get('db_password'));
			}
		}
		catch(\PDOException $e) {
			flash()->overlay("Can't connect to the database with the information provided", "Whoops!");
			return (redirect()->back()->withInput());
		}
		finally{
			$dbc = null; //required to close PDO connection
		}


		$status = $this->writeEnv($envstrings);

		if($status == true){
			$request->session()->put("adminuser",$adminuser); //Pass user data to next method
			return redirect('/install/migrate');
		}
		else{
			flash()->overlay("Your settings couldn't be saved. Make sure that PHP has permission to save the .env file","Whoops!");
			return redirect()->back()->withInput();
		}
	}

	private function writeEnv(Collection $envstrings)
	{
		$env_layout = "APP_ENV=local
			APP_DEBUG=true
			APP_KEY=SomeRandomString

			DB_HOST=" . $envstrings->get('db_host') . "\n" . "
			DB_DATABASE=" . $envstrings->get('db_database') . "\n" . "
			DB_USERNAME=" . $envstrings->get('db_username') . "\n" . "
			DB_PASSWORD=" . $envstrings->get('db_password') . "\n" . "
			DB_DEFAULT=" . $envstrings->get('db_driver') . "\n" . "
			DB_PREFIX=" . $envstrings->get('db_prefix') . "\n

			MAIL_HOST=" . $envstrings->get('mail_host') . "\n
			MAIL_FROM_ADDRESS=" . $envstrings->get('mail_from_address') . "\n
			MAIL_FROM_NAME=" . $envstrings->get('mail_from_name') . "\n
			MAIL_USER=" . $envstrings->get('mail_username') . "\n
			MAIL_PASSWORD=" . $envstrings->get('mail_password') . "\n

			CACHE_DRIVER=file
			SESSION_DRIVER=file

			BASE_URL=http://" . $envstrings->get('baseurl_url') . "\n
			BASE_PATH=" . $envstrings->get('basepath') . "\n

			RECAPTCHA_PUBLIC_KEY=" . $envstrings->get('recaptcha_public_key') . "\n
			RECAPTCHA_PRIVATE_KEY=" . $envstrings->get('recaptcha_private_key') . "\n
			";

		if (file_exists('../.env')) {
			return false;
		} else {
			try {
				$envfile = fopen("../.env", "w");
			} catch (\Exception $e) { //Most likely if the file is owned by another user or PHP doesn't have permission
				return false;
			}

			if (!fwrite($envfile, $env_layout)) { //write to file and if nothing is written or error
				fclose($envfile);
				return false;
			} else {
				fclose(($envfile));
				return true;
			}
		}
	}

    public function runMigrate(\Illuminate\Http\Request $request){
			if(!file_exists("../.env")){
				//flash()->overlay("The database connection settings do not exist",'Whoops!');
				return redirect('/install');
			}
			else{
				try {
						if(Schema::hasTable("users")){ //This indicates a migration has already been run
							return redirect('/');
						}
				}
				catch(\Exception $e){
					flash()->overlay("Double check the database connection settings","Whoops!");
					return redirect('/install');
				}
				try {
					$status = Artisan::call("migrate", array('--force' => true));
				}
				catch(\Exception $e){
					flash()->overlay("Sorry, couldn't run the Artisan migrations, please check Laravel's logs for details. ","Whoops!");
					return redirect('/');
				}
                try{
                    $status = Artisan::call("key:generate");
                }
                catch(\Exception $e){
                    flash()->overlay("Sorry, couldn't generate the application key through Artisan, please check Laravel's logs for details. ","Whoops!");
                    return redirect('/');
                }

                try{
                    $status = $this->createDirectories();
                }
                catch(\Exception $e){
                    flash()->overlay("Sorry, there was a problem creating some required directories.","Whoops!");
                    return redirect('/');
                }

				try{
					$adminuser = $request->session()->get('adminuser');

					$username = $adminuser->get('user_username');
					$name = $adminuser->get('user_realname');
					$email = $adminuser->get('user_email');
					$password = bcrypt($adminuser->get('user_password'));
					$organization = "";
					$language = $adminuser->get('user_language');

					$newuser = \App\User::create(compact("username","name","email","password","organization","language"));
					$newuser->active = 1;
					$newuser->admin = 1;
					$newuser->save();
				}
				catch(\Exception $e){
					flash()->overlay("The admin user account couldn't be created.","Whoops!");
				}
				finally{
					return redirect("/");
				}
			}
		}

    public function createDirectories(){
        foreach($this->DIRECTORIES as $dir){
            if(file_exists(ENV("BASE_PATH").$dir)){
                continue;
            }
            else{
                mkdir(ENV("BASE_PATH").$dir,0644); //Notice the permission that is set and if it's OK!
            }
        }
    }

}