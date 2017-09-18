<?php namespace App\Http\Controllers;

use App\Association;
use App\AssociatorField;
use App\Commands\SaveKora2Scheme;
use App\Form;
use App\FormGroup;
use App\OptionPreset;
use App\Project;
use App\ProjectGroup;
use App\Token;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ExodusController extends Controller {

    /*
    |--------------------------------------------------------------------------
    | Exodus Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the Exodus migration of Kora 2 data to Kora3
    |
    */

    /**
     * @var string - Views for the typed field options
     */
    const EXODUS_CONVERSION_PATH = "storage/app/exodusAssoc/conversions/";
    const EXODUS_DATA_PATH = "storage/app/exodusAssoc/data/";
    const EXODUS_FIELDOPT_PATH = "storage/app/exodusAssoc/fieldopt/";

    /**
     * Constructs controller and makes sure user is the root installation user.
     */
    public function __construct() {
        $this->middleware('auth');
        $this->middleware('active');
        $this->middleware('admin');
        if(Auth::check()) {
            if(Auth::user()->id != 1)
                return redirect("/projects")->with('k3_global_error', 'not_admin')->send();
        }
    }

    /**
     * Returns the view for the Exodus tool.
     *
     * @return View
     */
    public function index() {
        return view('exodus.index');
    }

    /**
     * Initializes the migration process and returns the progress view.
     *
     * @param  Request $request
     * @return View
     */
    public function migrate(Request $request) {
        $users_exempt_from_lockout = new Collection();
        $users_exempt_from_lockout->put(1,1); //Add another one of these with (userid,userid) to exempt extra users
        $this->lockUsers($users_exempt_from_lockout);

        //MySQL Info
        $host = $request->host;
        $name = $request->name;
        $user = $request->user;
        $pass = $request->pass;

        $migrateUsers = isset($request->users) ? 1 : 0;
        $migrateTokens = isset($request->tokens) ? 1 : 0;
        $projects = $request->projects;
        $filePath = $request->filePath;

        return view('exodus.progress',compact('host', 'name', 'user', 'pass', 'migrateUsers', 'migrateTokens', 'projects', 'filePath'));
    }

    /**
     * Gets a list of all the Kora 2 projects to be migrated.
     *
     * @param  Request $request
     * @return array - The list of projects
     */
    public function getProjectList(Request $request) {
        $con = mysqli_connect($request->host, $request->user, $request->pass, $request->name);

        $projectArray = array();

        $projects = $con->query("select * from project");
        while($p = $projects->fetch_assoc()) {
            $projectArray[$p['pid']] = $p['name'];
        }

        return $projectArray;
    }

    /**
     * Actually initiates the Exodus process.
     *
     * @param  Request $request
     */
    public function startExodus(Request $request) {
        $con = mysqli_connect($request->host,$request->user,$request->pass,$request->name);
        $dbInfo = array();
        $dbInfo['host'] = $request->host;
        $dbInfo['user'] = $request->user;
        $dbInfo['name'] = $request->name;
        $dbInfo['pass'] = $request->pass;

        //migrate booleans
        $migrateUsers = $request->migrateUsers;
        $migrateTokens = $request->migrateTokens;
        //list of projects to migrate
        $migratedProjects = explode(",",$request->projects);
        //file path of kora files
        $filePath = $request->filePath;

        $userArray = array();
        $projectArray = array();
        $formArray = array();
        $pairArray = array();
        $permArray = array();
        $tokenArray = array();

        $filePath = $request->filePath;

        //clear assoc directories
        $this->recursiveRemoveDirectoryFiles(env('BASE_PATH').self::EXODUS_CONVERSION_PATH);
        $this->recursiveRemoveDirectoryFiles(env('BASE_PATH').self::EXODUS_DATA_PATH);

        //we should do the user table and project related tables and then divide all the scheme tasks into queued jobs

        //Users
        $users = $con->query("select * from user where username!='koraadmin'");
        while($u = $users->fetch_assoc()) {
            if($u['salt']!=0 && $migrateUsers) {
                $email = $u['email'];
                if(!$this->emailExists($email)) {
                    $username = explode('@', $email)[0];
                    $i = 1;
                    $username_array = array();
                    $username_array[0] = $username;

                    // Increment a count while the username exists.
                    while($this->usernameExists($username)) {
                        $username_array[1] = $i;
                        $username = implode($username_array);
                        $i++;
                    }

                    //
                    // Create the new user.
                    //
                    $user = new User();
                    $user->username = $username;
                    $user->email = $email;
                    $user->name = $u['realName'];
                    $user->admin = $u['admin'];
                    $user->organization = $u['organization'];
                    $password = $this->passwordGen();
                    $user->password = bcrypt($password);
                    $token = AuthenticatesAndRegistersUsers::makeRegToken();
                    $user->regtoken = $token;
                    $user->save();

                    //add user to conversion array with new id
                    $userArray[$u['uid']] = $user->id;
                } else {
                    //add user to conversion using existing id so it's still relevant
                    $user = User::where('email', '=', $email)->first();
                    $userArray[$u['uid']] = $user->id;
                }
            } else {
                //salt is zero so we have a token and not a user
                $tid = $u['uid'];
                $token = $u['username'];
                $projects = array();
                $pids = $con->query("select * from member where uid=".$tid);
                while($pid = $pids->fetch_assoc()) {
                    if(in_array($pid['pid'],$migratedProjects))
                        array_push($projects,$pid['pid']);
                }
                //save for later because we need to build new projects first
                $tokenArray[$token] = $projects;
            }
        }

        //Projects
        $projects = $con->query("select * from project");
        $koraSysAdmins = User::where("admin","=",1)->get(); //See Below
        while($p = $projects->fetch_assoc()) {
            if(in_array($p['pid'],$migratedProjects)) {
                //make project
                $proj = new Project();
                $proj->name = $p['name'];
                $slug = str_replace(' ', '_', $p['name']);
                if(Project::where('slug', '=', $slug)->exists()) {
                    $unique = false;
                    $i = 1;
                    while(!$unique) {
                        if(Project::where('slug', '=', $slug . $i)->exists()) {
                            $i++;
                        } else {
                            $proj->slug = $slug . $i;
                            $unique = true;
                        }
                    }
                } else {
                    $proj->slug = $slug;
                }
                $proj->description = $p['description'];
                $proj->active = $p['active'];
                $proj->save();

                //add to project conversion array
                $projectArray[$p['pid']] = $proj->pid;

                //Before we create the permissions group, add this project to any system admin custom list
                foreach($koraSysAdmins as $admin) {
                    $admin->addCustomProject($proj->pid);
                }

                //create permission groups
                $permGroups = $con->query("select * from permGroup where pid=" . $p['pid']);
                while($pg = $permGroups->fetch_assoc()) {
                    $admin = false;
                    $k3Group = new ProjectGroup();
                    if($pg['name'] == 'Administrators') {
                        $k3Group->name = $proj->name . ' Admin Group';
                        $admin = true;
                    } else if($pg['name'] == 'Default') {
                        $k3Group->name = $proj->name . ' Default Group';
                    } else {
                        $k3Group->name = $pg['name'];
                    }
                    $k3Group->pid = $proj->pid;
                    $k3Group->save();

                    //this group is the admin group so save that info to the project
                    if($admin) {
                        $proj->adminGID = $k3Group->id;
                        $proj->save();
                    }

                    //add all the members to their appropriate groups
                    if($migrateUsers) {
                        $groupUsers = array();
                        $members = $con->query("select * from member where gid=" . $pg['gid']);
                        while($m = $members->fetch_assoc()) {
                            if(isset($userArray[$m['uid']]))
                                $gu = $userArray[$m['uid']];
                            else
                                continue; //most likely get here because k2 Admin was added as a group user, but no need for that in kora 3
                            //Add project to users custom list
                            $guModel = User::where("id","=",$gu)->first();
                            $guModel->addCustomProject($proj->pid);
                            array_push($groupUsers, $gu);
                        }
                        $k3Group->users()->attach($groupUsers);
                    }

                    //this part is going to be interesting. especially at the form level
                    $perms = $this->k2tok3Perms($pg['permissions']);
                    //lets pair these permissions with their group id so we can reference it when we make the form groups
                    $permArray[$k3Group->id] = $perms;
                    $k3Group->create = $perms['pCreate'];
                    $k3Group->edit = $perms['pEdit'];
                    $k3Group->delete = $perms['pDelete'];
                    $k3Group->save();
                }
            }
        }

        //Back to tokens
        if($migrateTokens) {
            foreach($tokenArray as $t => $tokenProjs) {
                $token = new Token();
                $token->token = $t;
                $token->title = "Kora 2 Search Token";
                $token->search = 1;
                $token->create = 0;
                $token->edit = 0;
                $token->delete = 0;
                $token->save();

                //add all it's projects
                foreach($tokenProjs as $tpid) {
                    $newPid = $projectArray[$tpid];
                    DB::table('project_token')->insert(
                        ['project_id' => $newPid, 'token_id' => $token->id]
                    );
                }
            }
        }

        //Option Presets
        $optPresets = $con->query("select * from controlPreset");
        while($o = $optPresets->fetch_assoc()) {
            if($o['project']==0 | !isset($projectArray[$o['project']])) {
                continue; //this is either an old global preset, or doesn't belong to a migrated project
            }

            $optionPID = $projectArray[$o['project']];

            switch($o['class']) {
                case 'TextControl':
                    if($o['value']!='') {
                        $preset = OptionPreset::create(['pid' => $optionPID, 'type' => 'Text', 'name' => $o['name'], 'preset' => $o['value']]);
                        $preset->save();
                        if($o['global']) {
                            $preset->shared = 1;
                        } else {
                            $preset->shared = 0;
                        }
                        $preset->save();
                    }
                    break;
                case 'ListControl':
                    $xml = simplexml_load_string(utf8_encode($o['value']));
                    $options = array();
                    if(!is_null($xml->option)) {
                        foreach((array)$xml->option as $opt) {
                            if($opt!=''){array_push($options,$opt);}
                        }
                    }
                    if(sizeof($options)>0) {
                        $optString = implode('[!]',$options);
                        $preset = OptionPreset::create(['pid' => $optionPID, 'type' => 'List', 'name' => $o['name'], 'preset' => $optString]);
                        $preset->save();
                        if($o['global']) {
                            $preset->shared = 1;
                        } else {
                            $preset->shared = 0;
                        }
                        $preset->save();
                    }
                    break;
            }
        }

        //Forms
        $forms = $con->query("select * from scheme");
        $masterAssoc = array();
        while($f = $forms->fetch_assoc()) {
            if(in_array($f['pid'],$migratedProjects)) {
                //make form
                $form = new Form();
                $form->pid = $projectArray[$f['pid']];
                $form->name = $f['schemeName'];
                $slug = str_replace(' ', '_', $f['schemeName']);
                if(Form::where('slug', '=', $slug)->exists()) {
                    $unique = false;
                    $i = 1;
                    while(!$unique) {
                        if(Form::where('slug', '=', $slug . $i)->exists()) {
                            $i++;
                        } else {
                            $form->slug = $slug . $i;
                            $unique = true;
                        }
                    }
                } else {
                    $form->slug = $slug;
                }
                $form->description = $f['description'];
                $form->preset = $f['allowPreset'];;
                $form->public_metadata = 0;
                $form->save();

                //add to form conversion array
                $formArray[$f['schemeid']] = $form->fid;
                //add to old sid/pid array
                $pairArray[$f['schemeid']] = $f['pid'];

                //We need to replicate the association permissions
                $assocXML = simplexml_load_string(utf8_encode($f['crossProjectAllowed']));
                //Checks if DB value is straight up null
                if($assocXML !== false) {
                    $aSchemes = (array)$assocXML->from;
                    //This will be an array no matter what, so if it's empty, leave it alone
                    if(!empty($aSchemes)) {
                        //Foreach scheme that can associate this one, we add its sid and store it for later.
                        //We want to make sure all forms exist first before this information is actually used.
                        $newAS = array();
                        //When there's only one assocation, it makes it an object and not an array of that object, so check what data type
                        if(is_array($aSchemes["entry"])) {
                            foreach($aSchemes["entry"] as $aS) {
                                $asid = (int)$aS->scheme;
                                array_push($newAS, $asid);
                            }
                        } else {
                            //This is the case where there's only one
                            array_push($newAS, (int)$aSchemes["entry"]->scheme);
                        }
                        //What we'll reference later
                        $masterAssoc[$form->fid] = $newAS;
                    }
                }

                //Before we create the permissions group, add this form to any system admin custom list
                foreach($koraSysAdmins as $admin) {
                    $admin->addCustomForm($form->fid);
                }

                //create admin/default groups based on project groups
                $permGroups = $con->query("select * from permGroup where pid=" . $f['pid']);
                while($pg = $permGroups->fetch_assoc()) {
                    $admin = false;
                    $k3Group = new FormGroup();
                    if($pg['name'] == 'Administrators') {
                        $k3Group->name = $form->name . ' Admin Group';
                        $nameOfProjectGroup = ProjectController::getProject($form->pid)->name . ' Admin Group';
                        $admin = true;
                    } else if($pg['name'] == 'Default') {
                        $k3Group->name = $form->name . ' Default Group';
                        $nameOfProjectGroup = ProjectController::getProject($form->pid)->name . ' Default Group';
                    } else {
                        $k3Group->name = $pg['name'];
                        $nameOfProjectGroup = $pg['name'];
                    }
                    $k3Group->fid = $form->fid;
                    $k3Group->save();

                    //this group is the admin group so save that info to the project
                    if($admin) {
                        $form->adminGID = $k3Group->id;
                        $form->save();
                    }

                    //add all the members from the newly created project group to the respective form group
                    $groupUsers = array();
                    $projGroup = ProjectGroup::where('name', '=', $nameOfProjectGroup)->where('pid', '=', $form->pid)->first();
                    if($migrateUsers) {
                        foreach($projGroup->users()->get() as $user) {
                            $user->addCustomForm($form->fid);
                            array_push($groupUsers, $user->id);
                        }
                        $k3Group->users()->attach($groupUsers);
                    }

                    //get the perms from earlier
                    $perms = $permArray[$projGroup->id];
                    //lets pair these permissions with their group id so we can reference it when we make the form groups
                    $k3Group->create = $perms['fCreate'];
                    $k3Group->edit = $perms['fEdit'];
                    $k3Group->delete = $perms['fDelete'];
                    $k3Group->ingest = $perms['ingest'];
                    $k3Group->modify = $perms['modify'];
                    $k3Group->destroy = $perms['destroy'];
                    $k3Group->save();
                }
            }
        }

        //Resolve the assoc permissions
        foreach($masterAssoc as $fid => $asids) {
            foreach($asids as $asid) {
                //Make sure the scheme it's looking for actually was transfered
                if(isset($formArray[$asid])) {
                    $assoc = new Association();
                    $assoc->dataForm = $formArray[$asid];
                    $assoc->assocForm = $fid;
                    $assoc->save();
                }
            }
        }

        mysqli_close($con);

        ini_set('max_execution_time',0);
        Log::info("Begin Exodus");
        $exodus_id = DB::table('exodus_overall_progress')->insertGetId(['progress'=>0,'overall'=>0,'start'=>Carbon::now(),'created_at'=>Carbon::now(),'updated_at'=>Carbon::now()]);
        foreach($formArray as $sid=>$fid) {
            $job = new SaveKora2Scheme($sid, $fid, $formArray, $pairArray, $dbInfo, $filePath, $exodus_id);
            $this->dispatch($job->onQueue('exodus'));
        }

        Artisan::call('queue:listen', [
            '--queue' => 'exodus',
            '--timeout' => 72000
        ]);
    }

    /**
     * Returns the current progress of the Exodus migration.
     *
     * @param  Request $request
     * @return string - Json array of the progress
     */
    public function checkProgress(Request $request) {
        $overall = DB::table('exodus_overall_progress')->where('created_at',DB::table('exodus_overall_progress')->max('created_at'))->first();
        if(is_null($overall)) {
            return 'inprogress';
        }
        $partial = DB::table('exodus_partial_progress')->where('exodus_id',$overall->id)->get();

        return response()->json(["status"=>true,"message"=>"exodus_progress","overall"=>$overall,"partial"=>$partial],200);
    }

    /**
     * Finishes the Exodus process.
     *
     * @param  Request $request
     */
    public function finishExodus(Request $request) {
        //Stores the KID to RID conversions
        $masterConvertor = array();

        //Get all the conversion arrays for k2 KIDs to k3 RIDs
        $dir1 = env('BASE_PATH').self::EXODUS_CONVERSION_PATH;
        $iterator = new \DirectoryIterator($dir1);
        foreach($iterator as $fileinfo) {
            if($fileinfo->isFile()) {
                $data = file_get_contents($dir1.$fileinfo->getFilename());
                $dataArray = json_decode($data);

                if(!is_array($dataArray)) {
                    foreach($dataArray as $kid => $rid) {
                        $masterConvertor[$kid] = $rid;
                    }
                }
            }
        }

        //Get all the matchups of k3 Assoc Field ids to the k2 KID values
        $dir2 = env('BASE_PATH').self::EXODUS_DATA_PATH;
        $iterator = new \DirectoryIterator($dir2);
        foreach($iterator as $fileinfo) {
            if($fileinfo->isFile()) {
                $data = file_get_contents($dir2.$fileinfo->getFilename());
                $dataArray = json_decode($data);

                foreach($dataArray as $afid => $kidArray) {
                    $assocfield = AssociatorField::where("id","=",$afid)->first();
                    $ridArray = array();

                    foreach($kidArray as $kid) {
                        //We add the dummy k3 KID numbers because that's the format addRecords expects
                        array_push($ridArray,"0-0-".$masterConvertor[$kid]);
                    }

                    $assocfield->addRecords($ridArray);
                }
            }
        }
    }

    /**
     * Checks whether the username is already taken.
     *
     * @param  string $username - Username to compare
     * @return bool - The result of its existence
     */
    private function usernameExists($username) {
        return !is_null(User::where('username', '=', $username)->first());
    }

    /**
     * Checks whether the email is already taken.
     *
     * @param  string $email - Email to compare
     * @return bool - The result of its existence
     */
    private function emailExists($email) {
        return !is_null(User::where('email', '=', $email)->first());
    }

    /**
     * Generates a new 10-character password.
     *
     * @return string - The new password
     */
    private function passwordGen() {
        $valid = 'abcdefghijklmnopqrstuvwxyz';
        $valid .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $valid .= '0123456789';

        $password = '';
        for($i = 0; $i < 10; $i++) {
            $password .= $valid[( rand() % 62 )];
        }
        return $password;
    }

    /**
     * Converts Kora 2 permissions to Project/Form permissions in Kora3.
     *
     * @param  int $perm - The permissions set to apply
     * @return array - The new permission set in Kora3
     */
    private function k2tok3Perms($perm) {
        $result = array();

        $result['pCreate'] = ((int)$perm & 1);
        $result['pEdit'] = ((int)$perm & 1);
        $result['pDelete'] = ((int)$perm & 1);

        $result['fCreate'] = ((int)$perm & 16);
        $result['fEdit'] = ((int)$perm & 16);
        $result['fDelete'] = ((int)$perm & 32);

        $result['ingest'] = ((int)$perm & 2);
        $result['modify'] = ((int)$perm & 2);
        $result['destroy'] = ((int)$perm & 4);

        return $result;
    }

    /**
     * A recursive function for deleting a directory's contents.
     *
     * @param  string $directory - Name of directory to remove
     */
    private function recursiveRemoveDirectoryFiles($directory) {
        foreach(glob("{$directory}/*") as $file) {
            if(is_dir($file)) {
                $this->recursiveRemoveDirectoryFiles($file);
            } else {
                unlink($file);
            }
        }
    }

    /**
     * Locks all users to prevent them from logging in during the restore/backup process. That way data will not be
     *  manipulated during them.
     *
     * @param  Collection $exemptions - A list of users excempt from the lockout
     */
    public function lockUsers(Collection $exemptions) {
        $users = User::all();
        foreach($users as $user) {
            if($exemptions->has($user->id)) {
                continue;
            } else {
                $user->locked_out = true;
                $user->save();
            }
        }
    }

    /**
     * Unlocks any locked users.
     *
     * @return string - Success or error message
     */
    public function unlockUsers() {

        try {
            $users = User::all();
            foreach($users as $user) {
                $user->locked_out = false;
                $user->save();
            }
        } catch(\Exception $e) {
            return response()->json(["status"=>false,"message"=>"user_unlock_failed"],500);
        }
        return response()->json(["status"=>true,"message"=>"user_unlock_success"],200);
    }
}
