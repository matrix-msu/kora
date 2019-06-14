<?php namespace App\Http\Controllers;

use App\Association;
use App\FieldValuePreset;
use App\Form;
use App\FormGroup;
use App\Http\Controllers\Auth\RegisterController;
use App\Project;
use App\ProjectGroup;
use App\Token;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
     * @var string - Storage folders for association conversions
     */
    const EXODUS_CONVERSION_PATH = "app/exodus/kidConversions/";
    const EXODUS_DATA_PATH = "app/exodus/assocData/";

    /**
     * Constructs controller and makes sure user is the root installation user.
     */
    public function __construct() {
        $this->middleware('auth');
        $this->middleware('active');
        $this->middleware('admin');

        //Custom middleware for handling root user checks
        $this->middleware(function ($request, $next) {
            if (Auth::check())
                if (Auth::user()->id != 1)
                    return false;

            return $next($request);
        });
    }

    /**
     * Actually initiates the Exodus process.
     *
     * @param  Request $request
     */
    public function startExodus(Request $request) {
        echo "Prepping Exodus...\n";

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
        $userNameArray = array();
        $projectArray = array();
        $formArray = array();
        $pairArray = array();
        $permArray = array();
        $tokenArray = array();

        //clear assoc directories
        $this->recursiveRemoveDirectoryFiles(storage_path(self::EXODUS_CONVERSION_PATH));
        $this->recursiveRemoveDirectoryFiles(storage_path(self::EXODUS_DATA_PATH));

        //we should do the user table and project related tables and then divide all the scheme tasks into queued jobs

        //Users
        echo "Gathering user info...\n";

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
                    $user->admin = $u['admin'];
                    $user->active = 1;
                    $password = uniqid();
                    $user->password = bcrypt($password);
                    $token = RegisterController::makeRegToken();
                    $user->regtoken = $token;
                    $user->save();

                    $preferences = array();

                    //Assign new user preferences
                    $preferences['first_name'] = $u['realName'];
                    $preferences['last_name'] = '';
                    $preferences['organization'] = $u['organization'];
                    $preferences['language'] = 'en';
                    $preferences['profile_pic'] = '';
                    $preferences['use_dashboard'] = 1;
                    $preferences['logo_target'] = 2;
                    $preferences['proj_tab_selection'] = 2;
                    $preferences['form_tab_selection'] = 2;
                    $preferences['onboarding'] = 1;
                    $user->preferences = $preferences;
                    $user->save();

                    //add user to conversion array with new id
                    $userArray[$u['uid']] = $user->id;
                    $userNameArray[$u['username']] = $user->id;
                } else {
                    //add user to conversion using existing id so it's still relevant
                    $user = User::where('email', '=', $email)->first();
                    $userArray[$u['uid']] = $user->id;
                    $userNameArray[$u['username']] = $user->id;
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
        echo "Building projects...\n";

        $projects = $con->query("select * from project");
        while($p = $projects->fetch_assoc()) {
            if(in_array($p['pid'],$migratedProjects)) {
                //make project
                $proj = new Project();
                $proj->name = $p['name'];
                $proj->description = $p['description'];
                $proj->active = $p['active'];
                $proj->save();

                //make slug
                $proj->internal_name = str_replace(" ","_", $proj->name).'_'.$proj->id.'_';
                $proj->save();

                //add to project conversion array
                $projectArray[$p['pid']] = $proj->id;

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
                    $k3Group->project_id = $proj->id;
                    $k3Group->save();

                    //this group is the admin group so save that info to the project
                    if($admin) {
                        $proj->adminGroup_id = $k3Group->id;
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
        echo "Migrating search tokens...\n";

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
        echo "Building field value presets...\n";

        $optPresets = $con->query("select * from controlPreset");
        while($o = $optPresets->fetch_assoc()) {
            if($o['project']==0 | !isset($projectArray[$o['project']]))
                continue; //this is either an old global preset, or doesn't belong to a migrated project

            $optionPID = $projectArray[$o['project']];

            switch($o['class']) {
                case 'TextControl':
                    if($o['value']!='') {
                        if($o['global'])
                            $shared = 1;
                        else
                            $shared = 0;

                        $preset = ["name" => $o['name'],"type"=>"Regex","preset"=>$o['value']];
                        FieldValuePreset::create(['project_id' => $optionPID, 'preset' => $preset, 'shared' => $shared]);
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
                        if($o['global'])
                            $shared = 1;
                        else
                            $shared = 0;

                        $preset = ["name" => $o['name'],"type"=>"Regex","preset"=>$options];
                        FieldValuePreset::create(['project_id' => $optionPID, 'preset' => $preset, 'shared' => $shared]);
                    }
                    break;
            }
        }

        //Forms
        echo "Building forms...\n";

        $forms = $con->query("select * from scheme");
        $masterAssoc = array();
        while($f = $forms->fetch_assoc()) {
            if(in_array($f['pid'],$migratedProjects)) {
                //make form
                $form = new Form();
                $form->project_id = $projectArray[$f['pid']];
                $form->name = $f['schemeName'];
                $form->description = $f['description'];
                $form->preset = $f['allowPreset'];
                $form->save();

                //Create slug
                $form->internal_name = str_replace(" ","_", $form->name).'_'.$form->project_id.'_'.$form->id.'_';
                $form->save();

                //Make the form's records table
                $rTable = new \CreateRecordsTable();
                $rTable->createFormRecordsTable($form->id);

                //add to form conversion array
                $formArray[$f['schemeid']] = $form->id;
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
                        $masterAssoc[$form->id] = $newAS;
                    }
                }

                //create admin/default groups based on project groups
                $permGroups = $con->query("select * from permGroup where pid=" . $f['pid']);
                while($pg = $permGroups->fetch_assoc()) {
                    $admin = false;
                    $k3Group = new FormGroup();
                    if($pg['name'] == 'Administrators') {
                        $k3Group->name = $form->name . ' Admin Group';
                        $nameOfProjectGroup = ProjectController::getProject($form->project_id)->name . ' Admin Group';
                        $admin = true;
                    } else if($pg['name'] == 'Default') {
                        $k3Group->name = $form->name . ' Default Group';
                        $nameOfProjectGroup = ProjectController::getProject($form->project_id)->name . ' Default Group';
                    } else {
                        $k3Group->name = $pg['name'];
                        $nameOfProjectGroup = $pg['name'];
                    }
                    $k3Group->form_id = $form->id;
                    $k3Group->save();

                    //this group is the admin group so save that info to the project
                    if($admin) {
                        $form->adminGroup_id = $k3Group->id;
                        $form->save();
                    }

                    //add all the members from the newly created project group to the respective form group
                    $groupUsers = array();
                    $projGroup = ProjectGroup::where('name', '=', $nameOfProjectGroup)->where('project_id', '=', $form->project_id)->first();
                    if($migrateUsers) {
                        foreach($projGroup->users()->get() as $user) {
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
        echo "Connecting forms for associators...\n\n";

        foreach($masterAssoc as $fid => $asids) {
            $asidsUnique = array_unique($asids);
            foreach($asidsUnique as $asid) {
                //Make sure the scheme it's looking for actually was transfered
                if(isset($formArray[$asid])) {
                    $assoc = new Association();
                    $assoc->data_form = $formArray[$asid];
                    $assoc->assoc_form = $fid;
                    $assoc->save();
                }
            }
        }

        mysqli_close($con);

        ini_set('max_execution_time',0);
        Log::info("Begin Exodus");
        echo "Begin Exodus...\n";

        $exodus_id = DB::table('exodus_overall')->insertGetId(['progress'=>0,'total_forms'=>sizeof($formArray),'created_at'=>Carbon::now(),'updated_at'=>Carbon::now()]);
        foreach($formArray as $sid=>$fid) {
            $job = new ExodusHelperController();
            $job->migrateControlsAndRecords($sid, $fid, $formArray, $pairArray, $dbInfo, $filePath, $exodus_id, $userNameArray);
        }
    }

    /**
     * Finishes the Exodus process by completeing associations.
     */
    public function finishExodus() {
        Log::info("Finishing Exodus");
        echo "Building associations (May take a while)...\n";

        //Stores the KID to RID conversions
        $masterConvertor = array();

        //Get all the conversion arrays for k2 KIDs to k3 KIDs
        $dir1 = storage_path(self::EXODUS_CONVERSION_PATH);
        $iterator = new \DirectoryIterator($dir1);
        foreach($iterator as $fileinfo) {
            if($fileinfo->isFile()) {
                $data = file_get_contents($dir1.$fileinfo->getFilename());
                $dataArray = json_decode($data);

                if(!is_array($dataArray)) {
                    foreach($dataArray as $kid => $kid3) {
                        $masterConvertor[$kid] = $kid3;
                    }
                }
            }
        }

        //Get all the matchups of k3 Assoc Field ids to the k2 KID values
        $dir2 = storage_path(self::EXODUS_DATA_PATH);
        $iterator = new \DirectoryIterator($dir2);
        foreach($iterator as $fileinfo) {
            if($fileinfo->isFile()) {
                $data = file_get_contents($dir2.$fileinfo->getFilename());
                $dataArray = json_decode($data);

                foreach($dataArray as $kid2 => $flids) {
                    $record = RecordController::getRecord($masterConvertor[$kid2]);

                    foreach($flids as $flid => $kidArray) {
                        $newKids = array();

                        foreach($kidArray as $oldKid) {
                            $nKid = $masterConvertor[$oldKid];
                            $newKids[] = $nKid;
                        }
                        $record->{$flid} = json_encode($newKids);
                    }

                    $record->save();
                }
            }
        }

        Log::info("Exodus Complete");
        echo "Exodus Complete!\n";
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
}