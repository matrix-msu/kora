<?php namespace App\Http\Controllers;

use \Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
Use \Illuminate\Support\Facades\Request;
use \Illuminate\Support\Facades\Session;
use \Illuminate\Support\Facades\Config;
use \Illuminate\Support\Facades\Artisan;

class WelcomeController extends Controller {

	/*
	|--------------------------------------------------------------------------
	| Welcome Controller
	|--------------------------------------------------------------------------
	|
	| This controller renders the "marketing page" for the application and
	| is configured to only allow guests. Like most of the other sample
	| controllers, you are free to modify or remove it as you desire.
	|
	*/

    //Constructor causing a redirect loop error, it is solved by commenting it out.
    //Error occurs only when a user is logged in so I assumed it was caused by
    //this middleware redirecting to 'guest' middleware. -Ian

//
//	/**
//	 * Create a new controller instance.
//	 *
//	 * @return void
//	 */
//	public function __construct()
//	{
//		$this->middleware('guest');
//	}

	/**
	 * Show the application welcome screen to the user.
	 *
	 * @return Response
	 */
	public function index(Request $request)
	{

		$languages_available = Config::get('app.locales_supported');
		$not_installed = true;
		if(!file_exists("../.env")){
			return view('welcome',compact('languages_available','not_installed'));
		}else if(\Auth::guest()){
            return view('welcome',compact('languages_available'));
        }
		else if (\Auth::user()->dash){
            return redirect('/dashboard');
		}else{
            return redirect('/projects');
        }
	}

    public function dashboard(){
        if(\Auth::guest()) {
            return redirect('/');
        }
        //gather all sections for the dashboard and their blocks
        $sections = array();

        $results = DB::table('dashboard_sections')->where('uid','=',Auth::user()->id)->orderBy('order')->get();
        foreach($results as $sec){
            $s = array();
            $s['title'] = $sec->title;
            $s['id'] = $sec->id;

            $blocks = array();
            $blkResults = DB::table('dashboard_blocks')->where('bid','=',$sec->id)->orderBy('order')->get();
            foreach($blkResults as $blk){
                $b = array();
                $b['id'] = $blk->id;
                $b['type'] = $blk->type;

                $options = explode('[!]',$blk->options);
                switch($blk->type){
                    case 'Project':
                        $pid = $options[0];
                        $disOpts = explode(',',$options[1]);
                        $hidOpts = explode(',',$options[2]);

                        $project = ProjectController::getProject($pid);

                        $b['pid'] = $pid;
                        $b['name'] = $project->name;
                        $b['description'] = $project->description;
                        $b['displayedOpts'] = $disOpts;
                        $b['hiddenOpts'] = $hidOpts;
                        break;
                    case 'Favorite Projects':
                        $projects = array();
                        foreach($options as $pid){
                            $p = array();
                            $name = ProjectController::getProject($pid)->name;

                            $p['pid'] = $pid;
                            $p['name'] = $name;

                            array_push($projects,$p);
                        }
                        $b['projects'] = $projects;
                        break;
                    case 'Form':
                        $fid = $options[0];
                        $disOpts = explode(',',$options[1]);
                        $hidOpts = explode(',',$options[2]);

                        $form = FormController::getForm($fid);

                        $b['fid'] = $fid;
                        $b['name'] = $form->name;
                        $b['description'] = $form->description;
                        $b['displayedOpts'] = $disOpts;
                        $b['hiddenOpts'] = $hidOpts;
                        break;
                    case 'Favorite Forms':
                        $forms = array();
                        foreach($options as $fid){
                            $f = array();
                            $name = FormController::getForm($fid)->name;

                            $f['fid'] = $fid;
                            $f['name'] = $name;

                            array_push($forms,$f);
                        }
                        $b['forms'] = $forms;
                        break;
                    case 'Your Records; Modified':
                        //get record ids
                        $blkResults = DB::table('revisions')->where('owner','=',Auth::user()->id)->orderBy('created_at','desc')->get();
                        $blkrecords = array();
                        foreach($blkResults as $rec){
                            $recMod = RecordController::getRecord($rec->rid);
                            if(!is_null($recMod)) {
                                $kid = $recMod->kid;
                            }else{
                                $formMod = FormController::getForm($rec->fid);
                                $kid = $formMod->pid.'-'.$rec->fid.'-'.$rec->rid;
                            }

                            if(!in_array($kid,$blkrecords))
                                array_push($blkrecords,$kid);

                            if(sizeof($blkrecords)==15)
                                break;
                        }
                        $b['records'] = $blkrecords;
                        break;
                        break;
                    case 'Records You\'ve Modified':
                        //get record ids
                        $blkResults = DB::table('revisions')->where('userId','=',Auth::user()->id)->orderBy('created_at','desc')->get();
                        $blkrecords = array();
                        foreach($blkResults as $rec){
                            $recMod = RecordController::getRecord($rec->rid);
                            if(!is_null($recMod)) {
                                $kid = $recMod->kid;
                            }else{
                                $formMod = FormController::getForm($rec->fid);
                                $kid = $formMod->pid.'-'.$rec->fid.'-'.$rec->rid;
                            }

                            if(!in_array($kid,$blkrecords))
                                array_push($blkrecords,$kid);

                            if(sizeof($blkrecords)==15)
                                break;
                        }
                        $b['records'] = $blkrecords;
                        break;
                    case 'Plugins':
                        //get plugin names and menus
                        break;
                    case 'Kora News':
                        //TODO
                        break;
                    case 'Kora Twitter':
                        //TODO
                        break;
                    case 'Dominos':
                        $user = $options[0];
                        $pass = $options[1];

                        $b['user'] = $user;
                        $b['pass'] = $pass;
                        break;
                    case 'Note':
                        $title = $options[0];
                        $text = $options[1];

                        $b['title'] = $title;
                        $b['text'] = $text;
                        break;
                    default:
                        break;
                }

                array_push($blocks,$b);
            }

            $s['blocks'] = $blocks;

            array_push($sections,$s);
        }

        return view('dashboard', compact('sections'));
    }

    public  function setTemporaryLanguage(Request $request){
        $language = Request::input('templanguage');
        Session::put('guest_user_language',$language);
        return(trans('controller_welcome.visitor').$language);
    }
}