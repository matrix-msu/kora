<?php namespace App\Http\Controllers;

use App\Metadata;
use App\Version;
use Illuminate\Support\Collection;
use \Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
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
	| This controller handles generating the .env file a2pdo67a7nd running the artisan
	| migration so the rest of the controllers can function.  It also creates the
	| first user.  And sets the application key, and creates needed folders.
	*/

    //Any directory in this array will be created for you during install with 0644 permission
    public $DIRECTORIES = ["storage/app/backups",
		"storage/app/backups/user_upload",
		"storage/app/backups/files",
		"storage/app/tmpFiles",
		"storage/app/tmpImport",
		"storage/app/files",
		"storage/app/profiles",
		"storage/app/presetFiles",
		"storage/app/plugins"
	];

	/*****************************************************
	 * TODO: Add python .env file to installation process.
	 *****************************************************/

	public function index(Request $request)
	{

		if(file_exists("../.env")){
			return redirect('/');
		}
		$not_installed = true;
        $languages_available = Config::get('app.locales_supported');

		return view('install.install',compact('languages_available','not_installed'));
	}

	public function editEnvConfigs(){
		if(!Auth::check()){
			return redirect("/");
		}

		if(!Auth::user()->admin){
			flash()->overlay(trans('controller_install.admin'),trans('controller_install.whoops'));
			return redirect("/");
		}
		$configs = new Collection();
        $current_config = $this->getEnvConfigs();

        $configs->push(["Recaptcha Private Key",$current_config->get("recaptcha_private_key")]);
        $configs->push(["Recaptcha Public Key",$current_config->get("recaptcha_public_key")]);
        $configs->push(["Mail Host",$current_config->get("mail_host")]);
        $configs->push(["Mail User",$current_config->get("mail_username")]);
        $configs->push(["Mail Password",""]);

		return view('install.config',compact('configs'));
	}

	public function updateEnvConfigs(\Illuminate\Http\Request $request){
		if(!Auth::check()){
			return redirect("/");
		}

		if(!Auth::user()->admin){
			flash()->overlay(trans('controller_install.admin'),trans('controller_install.whoops'));
			return redirect("/");
		}
        $current_config = $this->getEnvConfigs();

        if($request->input("type") == "Recaptcha Public Key"){
            $current_config->forget("recaptcha_public_key");
            $current_config->put("recaptcha_public_key",$request->input("value"));

        }
        elseif($request->input("type") == "Recaptcha Private Key"){
            $current_config->forget("recaptcha_private_key");
            $current_config->put("recaptcha_private_key",$request->input("value"));
        }

        elseif($request->input("type") == "Mail Host"){
            $current_config->forget("mail_host");
            $current_config->put("mail_host",$request->input("value"));

        }

        elseif($request->input("type") == "Mail User"){
            $current_config->forget("mail_username");
            $current_config->put("mail_username",$request->input("value"));

        }

        elseif($request->input("type") == "Mail Password"){
            $current_config->forget("mail_password");
            $current_config->put("mail_password",$request->input("value"));
        }
        else{
            return response()->json(["status"=>false,"message"=>$request->input("type").trans('controller_install.cantchange')],500);
        }

        $write_status = $this->writeEnv($current_config,true);

        if($write_status == false){
            return response()->json(["status"=>false,"message"=>trans('controller_install.unable')],500);
        }
        else{
            return response()->json(["status"=>true,"message"=>trans('controller_install.updated')]);
        }

	}

    public function getEnvConfigs(){
        $env2 = new Collection();

		$env2->put("app_env",ENV("APP_ENV"));
        $env2->put("app_key",ENV(("APP_KEY")));
		$env2->put("app_debug",ENV("APP_DEBUG"));

		$env2->put("db_host",ENV("DB_HOST"));
		$env2->put("db_database",ENV("DB_DATABASE"));
		$env2->put("db_username",ENV("DB_USERNAME"));
		$env2->put("db_password",ENV("DB_PASSWORD"));
		$env2->put("db_driver",ENV("DB_DEFAULT"));
		$env2->put("db_prefix",ENV("DB_PREFIX"));

		$env2->put("mail_host",ENV("MAIL_HOST"));
		$env2->put("mail_from_address",ENV("MAIL_FROM_ADDRESS"));
		$env2->put("mail_from_name",ENV("MAIL_FROM_NAME"));
		$env2->put("mail_username",ENV("MAIL_USER"));
		$env2->put("mail_password",ENV("MAIL_PASSWORD"));

		$env2->put("baseurl_url",ENV("BASE_URL"));
		$env2->put("basepath",ENV("BASE_PATH"));

        $env2->put("recaptcha_public_key",ENV("RECAPTCHA_PUBLIC_KEY"));
        $env2->put("recaptcha_private_key",ENV("RECAPTCHA_PRIVATE_KEY"));

        return $env2;

    }


	private function writeEnv(Collection $envstrings, $overwrite = false)
	{

        $baseurl = $envstrings->get("baseurl_url");
        //Check if http:// is included in the base URL, and addi it if missing
        if(!preg_match("/(http)(.*)/",$baseurl)){
            $baseurl = "http://".$baseurl;
        }
        //Check for trailing slashes
        if(substr($baseurl,-1) != "/"){
            $baseurl = $baseurl."/";
            $envstrings->forget("baseurl_url");
            $envstrings->put("baseurl_url",$baseurl);
        }

		$env_layout = "APP_ENV=local
			APP_DEBUG=true".
			//APP_KEY=" . ENV("APP_KEY") . "\n
            "
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

			BASE_URL=" . $envstrings->get('baseurl_url') . "\n
			BASE_PATH=" . $envstrings->get('basepath') . "\n

			RECAPTCHA_PUBLIC_KEY=" . $envstrings->get('recaptcha_public_key') . "\n
			RECAPTCHA_PRIVATE_KEY=" . $envstrings->get('recaptcha_private_key') . "\n
			";


		if (file_exists('../.env') && $overwrite==false) {
			return false;
		} else {
			try {
				$envfile = fopen("../.env", "w");

			} catch (\Exception $e) { //Most likely if the file is owned by another user or PHP doesn't have permission
                flash()->overlay(trans('controller_install.openenv')."\n ".$e->getMessage());
				return false;
			}
            try {
                if (!fwrite($envfile, $env_layout)) { //write to file and if nothing is written or error
                    fclose($envfile);
                    flash()->overlay(trans('controller_install.writeenv'));
                    return false;
                } else {
                    fclose(($envfile));
                    chmod("../.env",0660);
                    return true;
                }
            }
            catch(\Exception $e){
                flash()->overlay(trans('controller_install.writeenv')."\n ".$e->getMessage());
                return false;
            }
		}
	}

    public function runMigrate(\Illuminate\Http\Request $request){

		$this->validate($request,[
			'user_username'=>'required|alpha_dash',
			'user_email'=>'required|email',
			'user_password'=>'required|same:user_confirmpassword',
			'user_confirmpassword'=>'required',
			'user_realname'=>'required',
			'user_language'=>'required',
		]);

		$adminuser = new Collection();
		$adminuser->put('user_username',$request->input("user_username"));
		$adminuser->put('user_email',$request->input("user_email"));
		$adminuser->put('user_password',$request->input('user_password'));
		$adminuser->put('user_realname',$request->input('user_realname'));
		$adminuser->put('user_language',$request->input('user_language'));

			if(!file_exists("../.env")){
				//flash()->overlay("The database connection settings do not exist",'Whoops!');
				//return redirect('/install');
				return response()->json(["status"=>false,"message"=>trans('controller_install.nodb')],500);
			}
			else{
				try {
						if(Schema::hasTable("users")){ //This indicates a migration has already been run
							//return redirect('/');
							return response()->json(["status"=>false,"message"=>trans('controller_install.kora3')],500);
						}
				}
				catch(\Exception $e){
					flash()->overlay(trans('controller_install.checkdb'),trans('controller_install.whoops'));
					//return redirect('/install');
					return response()->json(["status"=>false,"message"=>trans('controller_install.connfailed')],500);
				}
				try {
					$status = Artisan::call("migrate", array('--force' => true));
				}
				catch(\Exception $e){
					flash()->overlay(trans('controller_install.runartisan'),trans('controller_install.whoops'));
					//return redirect('/');
					return response()->json(["status"=>false,"message"=>trans('controller_install.artisanfail')],500);
				}
                try{
                    $status = Artisan::call("key:generate");
                }
                catch(\Exception $e){
                    flash()->overlay(trans('controller_install.appkey'),trans('controller_install.whoops'));
                    //return redirect('/');
					return response()->json(["status"=>false,"message"=>trans('controller_install.probkey')],500);
                }

                try{
                    $status = $this->createDirectories();
                }
                catch(\Exception $e){
                    flash()->overlay(trans('controller_install.createdir'),trans('controller_install.whoops'));
                    //return redirect('/');
					return response()->json(["status"=>false,"message"=>trans('controller_install.unabledir'),"exception"=>$e->getMessage()],500);
                }

				try{
					$v = new Version();
					$v->version = UpdateController::getCurrentVersion();
					$v->save();
				}
				catch(\Exception $e){
					flash()->overlay(trans('controller_install.currver'), trans('controller_install.whoops'));
					//return redirect('/');
					return response()->json(["status"=>false,"message"=>trans('controller_install.probver')],500);
				}

				try{

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
					flash()->overlay(trans('controller_install.adminuser'),trans('controller_install.whoops'));
					return response()->json(["status"=>false,"message"=>trans('controller_install.adminfail')],500);
				}
				finally{
					return redirect("/");
				}
			}
		}

	public function installKora(\Illuminate\Http\Request $request){
		/*if(file_exists("../.env")) {
            flash()->overlay(".env file already exists, can't overwrite", "Whoops!");
            return redirect('/');
        }*/

		if(!file_exists("../.env")){
			//flash()->overlay("The database connection settings do not exist",'Whoops!');
			//return redirect('/install');

		}
		else {
			try {
				if (Schema::hasTable("users")) { //This indicates a migration has already been run
					//return redirect('/');
					return response()->json(["status" => false, "message" => trans('controller_install.kora3')], 500);
				}
			} catch (\Exception $e) {
				flash()->overlay(trans('controller_install.checkdb'), trans('controller_install.whoops'));
				//return redirect('/install');
				return response()->json(["status" => false, "message" => trans('controller_install.connfailed')], 500);
			}

		}
		$envstrings = new Collection();
		$this->validate($request,[
			'db_driver'=>'required|in:mysql,pgsql,sqlsrv,sqlite',
			'db_host'=>'required_if:db_driver,mysql,pgsql,sqlsrv',
			'db_database'=>'required_if:db_driver,mysql,pgsql,sqlsrv|alpha_dash',
			'db_username'=>'required_if:db_driver,mysql,pgsql,sqlsrv',
			'db_password'=>'required_if:db_driver,mysql,pgsql,sqlsrv',
			'db_prefix'=>'required|alpha_dash',
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

		$envstrings->put("basepath",$request->input("basepath"));

		$baseurl = $request->input("baseurl_url");
		//Check if http:// is included in the base URL, and addi it if missing
		if(!preg_match("/(http)(.*)/",$baseurl)){
			$baseurl = "http://".$baseurl;
		}
		//Check for trailing slashes
		if(substr($baseurl,-1) != "/"){
			$baseurl = $baseurl."/";
		}

		$envstrings->put("baseurl_url",$baseurl);

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
			flash()->overlay(trans('controller_install.dbinfo'), trans('controller_install.whoops'));
			return response()->json(["status"=>false,"message"=>trans('controller_install.dbinfo')],500);
			//return (redirect()->back()->withInput());
		}
		finally{
			$dbc = null; //required to close PDO connection
		}


		$status = $this->writeEnv($envstrings);

		if($status == true){
			return response()->json(["status"=>true,"message"=>"success"],200);
		}
		else{
			flash()->overlay(trans('controller_install.php'),trans('controller_install.whoops'));
			return response()->json(["status"=>false,"message"=>trans('controller_install.permission')],500);
		}


		//return response()->json(["status"=>false,message=>"Kora 3 was not installed"],500);
	}

    public function createDirectories(){
        foreach($this->DIRECTORIES as $dir){
            if(file_exists(ENV("BASE_PATH").$dir)){
                //echo "EXISTS ";
                //echo '<br>';
                continue;
            }
            else{
                try {
                    echo "mkdir on ". ENV("BASE_PATH") . $dir . "\n";
                    echo '<br>';
                   // mkdir(ENV("BASE_PATH") . $dir, 0644); //Notice the permission that is set and if it's OK!
                    mkdir(ENV("BASE_PATH") . $dir, 0770); //Notice the permission that is set and if it's OK!
                }
                catch(\Exception $e){
                    echo "Error  " . $e->getMessage() . "\n";
                    //echo '<br>';
                }
            }
        }
    }

}