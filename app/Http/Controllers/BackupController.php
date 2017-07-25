<?php namespace App\Http\Controllers;

use App\Commands\RestoreTable;
use App\Commands\SaveAssociationsTable;
use App\Commands\SaveAssociatorFieldsTable;
use App\Commands\SaveAssociatorSupportTable;
use App\Commands\SaveComboListFieldsTable;
use App\Commands\SaveComboSupportTable;
use App\Commands\SaveDashboardBlocksTable;
use App\Commands\SaveDashboardSectionsTable;
use App\Commands\SaveDateFieldsTable;
use App\Commands\SaveDocumentsFieldsTable;
use App\Commands\SaveFieldsTable;
use App\Commands\SaveFormGroupsTable;
use App\Commands\SaveFormGroupUsersTable;
use App\Commands\SaveFormsTable;
use App\Commands\SaveGalleryFieldsTable;
use App\Commands\SaveGeneratedListFieldsTable;
use App\Commands\SaveGeolocatorFieldsTable;
use App\Commands\SaveGeolocatorSupportTable;
use App\Commands\SaveListFieldTable;
use App\Commands\SaveMetadatasTable;
use App\Commands\SaveModelFieldsTable;
use App\Commands\SaveMultiSelectListFieldsTable;
use App\Commands\SaveNumberFieldsTable;
use App\Commands\SaveOptionPresetsTable;
use App\Commands\SavePagesTable;
use App\Commands\SavePlaylistFieldsTable;
use App\Commands\SavePluginMenusTable;
use App\Commands\SavePluginSettingsTable;
use App\Commands\SavePluginsTable;
use App\Commands\SavePluginUsersTable;
use App\Commands\SaveProjectGroupsTable;
use App\Commands\SaveProjectGroupUsersTable;
use App\Commands\SaveProjectsTable;
use App\Commands\SaveProjectTokensTable;
use App\Commands\SaveRecordPresetsTable;
use App\Commands\SaveRecordsTable;
use App\Commands\SaveRevisionsTable;
use App\Commands\SaveRichTextFieldsTable;
use App\Commands\SaveScheduleFieldsTable;
use App\Commands\SaveScheduleSupportTable;
use App\Commands\SaveTextFieldsTable;
use App\Commands\SaveTokensTable;
use App\Commands\SaveUsersTable;
use App\Commands\SaveVideoFieldsTable;
use App\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Collection;
Use \Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use RecursiveIteratorIterator;
use Symfony\Component\Finder\Iterator\RecursiveDirectoryIterator;

class BackupController extends Controller {

    /*
    |--------------------------------------------------------------------------
    | Backup Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles creation of backup files, saving them as a restore
    | point, downloading them to the user's computer, restoring from a saved or
    | uploaded file, and locking and unlocking users during operations.
    |
    */

    /**
     * @var string- Sets the backup directory relative to laravel/storage/app
     */
    private $BACKUP_DIRECTORY = "backups";

    /**
     * Constructs the controller and makes sure active user is the root user
     */
    public function __construct() {
        $this->middleware('auth');
        $this->middleware('admin');
        $this->middleware('active');
        $this->middleware('admin');
        if(Auth::check()) {
            if(Auth::user()->id != 1) {
                flash()->overlay(trans('controller_backup.admin'),trans('controller_backup.whoops'));
                return redirect("/projects")->send();
            }
        }

        $this->ajax_error_list = new Collection(); //The Exception's getMessage() for data that didn't restore/backup
    }

    /**
     * Gets list of backups and returns view of the main backup page.
     *
     * @param  Request $request
     * @return View
     */
    public function index(Request $request) {
        try {
            $user_support = DB::table('backup_support')->where('user_id', Auth::user()->id)->where('view', 'backups.index')->first();
            if($user_support === null) {
                $user_support = DB::table('backup_support')->insert(['user_id' => Auth::user()->id, 'view' => 'backups.index', 'hasRun' => Carbon::now(), 'accessed' => 0, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
            } else {
                if((Carbon::createFromFormat('Y#m#d G#i#s', ($user_support->updated_at))->diffInMinutes(Carbon::now()) < 2)) {
                    if($user_support->accessed>0)
                        DB::table('backup_support')->where('id', $user_support->id)->update(['accessed' => $user_support->accessed - 1, 'updated_at' => Carbon::now()]);
                } else if ((Carbon::createFromFormat('Y#m#d G#i#s', ($user_support->hasRun))->diffInMinutes(Carbon::now()) > 30) && ($user_support->accessed % 10 == 0 && $user_support->accessed != 0)) {
                    DB::table('backup_support')->where('id', $user_support->id)->update(['hasRun' => Carbon::now(), 'accessed' => 0, 'updated_at' => Carbon::now()]);
                    $request->session()->flash('user_backup_support',true);
                } else {
                    DB::table('backup_support')->where('id', $user_support->id)->update(['accessed' => $user_support->accessed + 1, 'updated_at' => Carbon::now()]);
                }
            }
        }
        catch(\Exception $e) {
            $user_support = null;
        }

        $available_backups = array();
        foreach(new \DirectoryIterator(env('BASE_PATH')."storage/app/".$this->BACKUP_DIRECTORY."/") as $dir) {
            $name = $dir->getFilename();
            if(strpos($name, 'fileRestore') !== false)
                continue;
            if($name!='.' && $name!='..' && $dir->isDir()) {
                if(file_exists(env('BASE_PATH')."storage/app/".$this->BACKUP_DIRECTORY.'/'.$name.'/.kora3_backup'))
                    array_push($available_backups,$this->BACKUP_DIRECTORY.'/'.$name.'/.kora3_backup');
            }
        }
        $saved_backups = new Collection();

        //Load all previously saved backups, and package them up so they can be displayed by the view
        $available_backups_index = 0;
        foreach($available_backups as $backup) {
            $backup_info = new Collection();
            $backup_file = Storage::get($backup);
            $parsed_data = json_decode($backup_file);
            $backup_info->put("index",$available_backups_index); //We sort this later,  but it needs to refer to other
            $backup_info->put("filename",$backup); //We sort this later,  but it needs to refer to other
            $available_backups_index++;
            try {
                $backup_info->put("date", $parsed_data->kora3->date);
                $backup_info->put("timestamp",Carbon::parse($parsed_data->kora3->date)->timestamp);
            } catch(\Exception $e) {
                $backup_info->put("date","Unknown");
                $backup_info->put("timestamp",Carbon::now()->timestamp);
            }
            try {
                $backup_info->put("name", $parsed_data->kora3->name);
            } catch(\Exception $e) {
                $backup_info->put("name","Unknown");
            }
            try{
                $backup_info->put("user",$parsed_data->kora3->created_by);
            } catch(\Exception $e) {
                $backup_info->put("user","Unknown");
            }

            $saved_backups->push($backup_info);
        }
        $saved_backups = $saved_backups->sortByDesc(function($item) {
            return $item->get('timestamp');
        });

        return view('backups.index',compact('saved_backups'));
    }


    /**
     * Initiates the backup and returns progress view.
     *
     * @param  Request $request
     * @return View
     */
    public function startBackup(Request $request) {
        $this->validate($request,[
            'backup_label'=>'required|alpha_dash',
        ]);
        $backup_label = $request->input("backup_label").'___'.Carbon::now()->toDateTimeString();
        $type  = "system";
        return view('backups.backup',compact('backup_label','type'));
    }

    /**
     * Initializes backup, sets up basic info and all needed directories.
     *
     * @param  Request $request
     */
    public function create(Request $request) {
        $users_exempt_from_lockout = new Collection();
        $users_exempt_from_lockout->put(1,1); //Add another one of these with (userid,userid) to exempt extra users

        $this->lockUsers($users_exempt_from_lockout);

        $backup_name = $request->backup_name;

        $backup_filepath = $this->BACKUP_DIRECTORY."/".$backup_name;
        //Get an instance of Flysystem disk, to use Amazon AWS, SFTP, or Dropbox, change this!
        $backup_fs = Storage::disk('local');
        $backup_disk = "local";
        //
        $backup_fs->makeDirectory($backup_filepath);
        $this->saveDatabase2($backup_disk, $backup_filepath);

    }

    /**
     * Loads and executes the background save command for each table.
     *
     * @param  string $backup_disk - Back up file system type
     * @param  string $path - Path where JSON Outputs will be stored
     */
    public function saveDatabase2($backup_disk, $path) {
        ini_set('max_execution_time',0);
        Log::info("Backup fp: ".$path);
        $backup_id = DB::table('backup_overall_progress')->insertGetId(['progress'=>0,'overall'=>0,'start'=>Carbon::now(),'created_at'=>Carbon::now(),'updated_at'=>Carbon::now()]);
        //TODO::Can we make this more modular?
        $jobs = [new SaveFormsTable($backup_disk, $path, $backup_id ),
            new SaveProjectsTable($backup_disk, $path, $backup_id),
            new SaveRecordsTable($backup_disk, $path, $backup_id ),
            new SaveTextFieldsTable($backup_disk, $path, $backup_id ),
            new SaveComboListFieldsTable($backup_disk, $path, $backup_id),
            new SaveComboSupportTable($backup_disk, $path, $backup_id),
            new SaveDateFieldsTable($backup_disk, $path, $backup_id),
            new SaveFieldsTable($backup_disk, $path, $backup_id),
            new SaveGeneratedListFieldsTable($backup_disk, $path, $backup_id),
            new SaveGeolocatorFieldsTable($backup_disk, $path, $backup_id),
            new SaveGeolocatorSupportTable($backup_disk, $path, $backup_id),
            new SaveListFieldTable($backup_disk, $path, $backup_id),
            new SaveMetadatasTable($backup_disk, $path, $backup_id),
            new SaveMultiSelectListFieldsTable($backup_disk, $path, $backup_id),
            new SaveNumberFieldsTable($backup_disk, $path, $backup_id),
            new SaveOptionPresetsTable($backup_disk, $path, $backup_id),
            new SaveRecordPresetsTable($backup_disk, $path, $backup_id),
            new SaveProjectGroupsTable($backup_disk, $path, $backup_id),
            new SaveProjectGroupUsersTable($backup_disk, $path, $backup_id),
            new SaveFormGroupsTable($backup_disk, $path, $backup_id),
            new SaveFormGroupUsersTable($backup_disk, $path, $backup_id),
            new SaveRevisionsTable($backup_disk, $path, $backup_id),
            new SaveRichTextFieldsTable($backup_disk, $path, $backup_id),
            new SaveScheduleFieldsTable($backup_disk, $path, $backup_id),
            new SaveScheduleSupportTable($backup_disk, $path, $backup_id),
            new SaveDocumentsFieldsTable($backup_disk, $path, $backup_id),
            new SavePlaylistFieldsTable($backup_disk, $path, $backup_id),
            new SaveVideoFieldsTable($backup_disk, $path, $backup_id),
            new SaveGalleryFieldsTable($backup_disk, $path, $backup_id),
            new SaveModelFieldsTable($backup_disk, $path, $backup_id),
            new SaveAssociatorFieldsTable($backup_disk, $path, $backup_id),
            new SaveAssociatorSupportTable($backup_disk, $path, $backup_id),
            new SaveAssociationsTable($backup_disk, $path, $backup_id),
            new SaveTokensTable($backup_disk, $path, $backup_id),
            new SaveProjectTokensTable($backup_disk, $path, $backup_id),
            new SavePluginsTable($backup_disk, $path, $backup_id),
            new SavePluginMenusTable($backup_disk, $path, $backup_id),
            new SavePluginSettingsTable($backup_disk, $path, $backup_id),
            new SavePluginUsersTable($backup_disk, $path, $backup_id),
            new SaveDashboardBlocksTable($backup_disk, $path, $backup_id),
            new SaveDashboardSectionsTable($backup_disk, $path, $backup_id),
            new SaveUsersTable($backup_disk, $path, $backup_id),
            new SavePagesTable($backup_disk, $path, $backup_id)];

        foreach($jobs as $job) {
            //Queue::push($job);
            $this->dispatch($job->onQueue('backup'));
        }

        Artisan::call('queue:listen', [
            '--queue' => 'backup',
            '--timeout' => 1800
        ]);
    }

    /**
     * Checks the overall progress of the backup.
     *
     * @param  Request $request
     * @return string - A json array of the overall progress parts
     */
    public function checkProgress(Request $request) {
        //Total number of tables commands being saved
        $overall = DB::table('backup_overall_progress')->where('created_at',DB::table('backup_overall_progress')->max('created_at'))->first();
        //Number completed so far
        $partial = DB::table('backup_partial_progress')->where('backup_id',$overall->id)->get();

        return response()->json(["overall"=>$overall,"partial"=>$partial],200);
    }

    /**
     * After progress is complete, run this function to backup files and save the master backup file
     *
     * @param  Request $request
     * @return string - Returns string on error
     */
    public function finishBackup(Request $request) {
        ini_set('max_execution_time',0);
        $label = $request->backup_label;
        $labelParts = explode('___',$label);
        $name = $labelParts[0];
        $time = $labelParts[1];

        //time to move the files
        $filepath = env('BASE_PATH')."storage/app/files/";
        $newfilepath = env('BASE_PATH')."storage/app/".$this->BACKUP_DIRECTORY."/".$label."/files/";
        mkdir($newfilepath, 0775, true);
        $directory = new \RecursiveDirectoryIterator($filepath);
        $iterator = new \RecursiveIteratorIterator($directory);
        foreach($iterator as $file) {
            if($file->isFile()) {
                //get file name and sub directories
                $fPath = $file->getRealPath();
                $subPath = explode($filepath,$fPath)[1]; //sub directory + filename
                $fname = $file->getFilename(); //filename
                //if that files sub directory doesn't exist, make it
                $subDirArr = explode($fname,$subPath);
                $loopSize = sizeof($subDirArr)-1;
                $subDir = ''; //just the sub directory
                for($i=0;$i<$loopSize;$i++) {
                    $subDir .= $subDirArr[$i];
                }
                if(!file_exists($newfilepath.$subDir))
                    mkdir($newfilepath.$subDir, 0775, true);
                //copy file over
                copy($filepath.$subPath, $newfilepath.$subPath);
            }
        }

        //set up initial json
        $data = array();

        $k3 = array();
        $k3['date'] = $time;
        $k3['name'] = $name;
        $k3['user'] = Auth::user()->username;
        $k3['type'] = 'system_backup';

        //save json file
        $path = env('BASE_PATH')."storage/app/".$this->BACKUP_DIRECTORY."/".$label."/";
        $data['kora3'] = $k3;
        $json = json_encode($data);
        $newfile = $path . ".kora3_backup";

        $bytes_written = File::put($newfile, $json);
        if ($bytes_written === false)
        {
            echo "Error writing backup file";
        }

        $this->unlockUsers();

        $totalSize = $this->humanFileSize($this->getDirectorySize($path));

        return response()->json(["totalSize"=>$totalSize],200);
    }

    private function getDirectorySize($path) {
        $bytestotal = 0;
        $path = realpath($path);
        if($path!==false && $path!='' && file_exists($path)) {
            foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS)) as $object) {
                $bytestotal += $object->getSize();
            }
        }
        return $bytestotal;
    }

    private function humanFileSize($size,$unit="") {
        if( (!$unit && $size >= 1<<30) || $unit == "GB")
            return number_format($size/(1<<30),2)."GB";
        if( (!$unit && $size >= 1<<20) || $unit == "MB")
            return number_format($size/(1<<20),2)."MB";
        if( (!$unit && $size >= 1<<10) || $unit == "KB")
            return number_format($size/(1<<10),2)."KB";
        return number_format($size)." bytes";
    }

    /**
     * Download a zipped copy of the backup.
     *
     * @param  string $path - System path to the backup
     * @param  Request $request
     */
    public function download($path, Request $request) {
        $fullpath = env('BASE_PATH')."storage/app/".$this->BACKUP_DIRECTORY."/".$path."/";

        $zipname = $path.'.zip';
        $zipdir = env('BASE_PATH')."storage/app/".$this->BACKUP_DIRECTORY."/";
        $zip = new \ZipArchive();
        $zip->open($zipdir.$zipname, \ZipArchive::CREATE);

        $directory = new \RecursiveDirectoryIterator($fullpath);
        $iterator = new \RecursiveIteratorIterator($directory);
        foreach($iterator as $info) {
            if($info->getFilename() != '.' && $info->getFilename() != '..') {
                $fPath = $info->getRealPath();
                $subPath = explode($path."/",$fPath)[1];
                $zip->addFile($fPath,$subPath);
            }
        }

        $zip->close();

        header('Content-Type: application/zip');
        header('Content-disposition: attachment; filename='.$zipname);
        header('Content-Length: ' . filesize($zipdir.$zipname));
        readfile($zipdir.$zipname);
    }

    /**
     * Initiates the restore process and returns the restore view.
     *
     * @param  Request $request
     * @return View - The
     */
    public function startRestore(Request $request) {


        $this->validate($request,[
            'backup_source'=>'required|in:server,upload',
            'restore_point'=>'required_if:backup_source,server',
            'upload_file'=>'required_if:backup_source,upload'
        ]);

        $type = "system";
        if($request->input("backup_source") == "server") {
            $filename = $request->restore_point;
            //we only want the directory now so strip the .kora3_backup tag
            $filename = explode('/.kora3_backup',$filename)[0];
        } else if($request->input("backup_source") == "upload") {
            if($request->hasFile("upload_file") == true) {
                $file = $request->file("upload_file");
                if($file->isValid()) {
                    //Once we have a file, we need to do two things
                    //First, save the file name path to a variable
                    $filename = "backups/fileRestore___".time();
                    $filepath = env("BASE_PATH")."storage/app/".$filename;
                    mkdir($filepath, 0775, true);
                    try {
                        //Second, unzip the file into the backups directory
                        $zip = new \ZipArchive();
                        $res = $zip->open($file->getRealPath());
                        if($res === TRUE) {
                            $zip->extractTo($filepath);
                            $zip->close();
                        } else {
                            flash()->overlay('Zip extraction failed!','code: ' . $res);
                            return redirect()->back();
                        }
                    } catch(\Exception $e) {
                        flash()->overlay(trans('controller_backup.cantmove'),trans('controller_backup.whoops'));
                        return redirect()->back();
                    }
                } else {
                    flash()->overlay(trans('controller_backup.badfile'),trans('controller_backup.whoops'));
                    return redirect()->back();
                }
            } else {
                flash()->overlay(trans('controller_backup.nofiles'),trans('controller_backup.whoops'));
                return redirect()->back();
            }
        } else {
            return redirect()->back();
        }

        dd($filename);

        return view('backups.restore',compact('type','filename'));
    }

    /**
     * Loads and executes the background restore command for each table.
     *
     * @param  Request $request
     */
	public function restoreData(Request $request) {

        //Lock out users
        $users_exempt_from_lockout = new Collection();
        $users_exempt_from_lockout->put(1,1); //Add another one of these with (userid,userid) to exempt extra users

        $this->lockUsers($users_exempt_from_lockout);

        //We need to gather the directory where the restored files are
        $dir = env('BASE_PATH').'storage/app/'.$request->filename;

        //Delete all existing data
        try {
            foreach(User::all() as $User) {
                if($User->id == 1) { //Do not delete the default admin user
                    continue;
                } else {
                    $User->delete();
                }
            }
            //TODO::Can we make this more modular?
            DB::table('projects')->delete();
            DB::table('forms')->delete();
            DB::table('pages')->delete();
            DB::table('fields')->delete();
            DB::table('records')->delete();
            DB::table('metadatas')->delete();
            DB::table('tokens')->delete();
            DB::table('project_token')->delete();
            DB::table('revisions')->delete();
            DB::table('date_fields')->delete();
            DB::table('form_groups')->delete();
            DB::table('form_group_user')->delete();
            DB::table('generated_list_fields')->delete();
            DB::table('geolocator_fields')->delete();
            DB::table('geolocator_support')->delete();
            DB::table('list_fields')->delete();
            DB::table('multi_select_list_fields')->delete();
            DB::table('number_fields')->delete();
            DB::table('project_groups')->delete();
            DB::table('project_group_user')->delete();
            DB::table('rich_text_fields')->delete();
            DB::table('schedule_fields')->delete();
            DB::table('schedule_support')->delete();
            DB::table('text_fields')->delete();
            DB::table('documents_fields')->delete();
            DB::table('model_fields')->delete();
            DB::table('gallery_fields')->delete();
            DB::table('video_fields')->delete();
            DB::table('playlist_fields')->delete();
            DB::table('combo_list_fields')->delete();
            DB::table('combo_support')->delete();
            DB::table('associator_fields')->delete();
            DB::table('associator_support')->delete();
            DB::table('associations')->delete();
            DB::table('option_presets')->delete();
            DB::table('record_presets')->delete();
            DB::table('plugins')->delete();
            DB::table('plugin_menus')->delete();
            DB::table('plugin_settings')->delete();
            DB::table('plugin_users')->delete();
            DB::table('dashboard_sections')->delete();
            DB::table('dashboard_blocks')->delete();
            DB::table('pages')->delete();


        } catch(\Exception $e) {
            $this->ajaxResponse(false, trans('controller_backup.dbpermission'));
        }

        //Delete the files directory
        if(file_exists(env('BASE_PATH')."storage/app/files/")) //this check is to see if it was deleted in a failed restore
            $this->recursiveRemoveDirectory(env('BASE_PATH')."storage/app/files/");

        //NEW PROCESS For restore using jobs
        ini_set('max_execution_time',0);
        Log::info("Restore in progress...");
        $restore_id = DB::table('restore_overall_progress')->insertGetId(['progress'=>0,'overall'=>0,'start'=>Carbon::now(),'created_at'=>Carbon::now(),'updated_at'=>Carbon::now()]);
        //These jobs need restore versions. Will test with TEXT

        //TODO::Can we make this more modular?
        $jobs = [new RestoreTable("users",$dir, $restore_id),
            new RestoreTable('projects',$dir, $restore_id),
            new RestoreTable('forms',$dir, $restore_id),
            new RestoreTable('pages',$dir, $restore_id),
            new RestoreTable('fields',$dir, $restore_id),//
            new RestoreTable('records',$dir, $restore_id),
            new RestoreTable('metadatas',$dir, $restore_id),
            new RestoreTable('tokens',$dir, $restore_id),
            new RestoreTable('project_token',$dir, $restore_id),
            new RestoreTable('revisions',$dir, $restore_id),//
            new RestoreTable('date_fields',$dir, $restore_id),
            new RestoreTable('form_groups',$dir, $restore_id),
            new RestoreTable('form_group_user',$dir, $restore_id),
            new RestoreTable('generated_list_fields',$dir, $restore_id),
            new RestoreTable('geolocator_fields',$dir, $restore_id),//
            new RestoreTable('geolocator_support',$dir, $restore_id),
            new RestoreTable('list_fields',$dir, $restore_id),
            new RestoreTable('multi_select_list_fields',$dir, $restore_id),
            new RestoreTable('number_fields',$dir, $restore_id),
            new RestoreTable('project_groups',$dir, $restore_id),//
            new RestoreTable('project_group_user',$dir, $restore_id),
            new RestoreTable('rich_text_fields',$dir, $restore_id),
            new RestoreTable('schedule_fields',$dir, $restore_id),
            new RestoreTable('schedule_support',$dir, $restore_id),
            new RestoreTable('text_fields',$dir, $restore_id),//
            new RestoreTable('documents_fields',$dir, $restore_id),
            new RestoreTable('model_fields',$dir, $restore_id),
            new RestoreTable('gallery_fields',$dir, $restore_id),
            new RestoreTable('video_fields',$dir, $restore_id),
            new RestoreTable('playlist_fields',$dir, $restore_id),//
            new RestoreTable('combo_list_fields',$dir, $restore_id),
            new RestoreTable('combo_support',$dir, $restore_id),
            new RestoreTable('associator_fields',$dir, $restore_id),
            new RestoreTable('associator_support',$dir, $restore_id),
            new RestoreTable('associations',$dir, $restore_id),//
            new RestoreTable('option_presets',$dir, $restore_id),
            new RestoreTable('record_presets',$dir, $restore_id),
            new RestoreTable('plugins',$dir, $restore_id),
            new RestoreTable('plugin_menus',$dir, $restore_id),
            new RestoreTable('plugin_settings',$dir, $restore_id),//
            new RestoreTable('plugin_users',$dir, $restore_id),
            new RestoreTable('dashboard_sections',$dir, $restore_id),
            new RestoreTable('dashboard_blocks',$dir, $restore_id),];

        foreach($jobs as $job) {
            $this->dispatch($job->onQueue('restore'));
        }

        Artisan::call('queue:listen', [
            '--queue' => 'restore',
            '--timeout' => 1800
        ]);
	}

    /**
     * Checks the overall progress of the restore.
     *
     * @param  Request $request
     * @return string - A json array of the overall progress parts
     */
    public function checkRestoreProgress(Request $request) {
        $overall = DB::table('restore_overall_progress')->where('created_at',DB::table('restore_overall_progress')->max('created_at'))->first();
        $partial = DB::table('restore_partial_progress')->where('restore_id',$overall->id)->get();

        return response()->json(["overall"=>$overall,"partial"=>$partial],200);
    }

    /**
     * After progress is complete, run this function to backup files and save the master backup file.
     *
     * @param  Request $request
     */
    public function finishRestore(Request $request) {
        $filepath = env('BASE_PATH').'storage/app/'.$request->filename.'/files/';
        $newfilepath = env('BASE_PATH')."storage/app/files/";

        //time to move the files
        mkdir($newfilepath, 0775, true);
        $directory = new \RecursiveDirectoryIterator($filepath);
        $iterator = new \RecursiveIteratorIterator($directory);
        foreach($iterator as $file) {
            if($file->isFile()) {
                //get file name and sub directories
                $fPath = $file->getRealPath();
                $subPath = explode($filepath,$fPath)[1]; //sub directory + filename
                $fname = $file->getFilename(); //filename
                //if that files sub directory doesn't exist, make it
                //$subDir = preg_replace('/'.$fname.'$/', '', $subPath); //just the sub directory
                $subDirArr = explode($fname,$subPath);
                $loopSize = sizeof($subDirArr)-1;
                $subDir = ''; //just the sub directory
                for($i=0;$i<$loopSize;$i++) {
                    $subDir .= $subDirArr[$i];
                }
                if(!file_exists($newfilepath.$subDir))
                    mkdir($newfilepath.$subDir, 0775, true);
                //copy file over
                copy($filepath.$subPath, $newfilepath.$subPath);
            }
        }
    }

    /**
     * A recursive function for deleting a directory and all its contents.
     *
     * @param  string $directory - Name of directory to remove
     */
    private function recursiveRemoveDirectory($directory) {
        foreach(glob("{$directory}/*") as $file) {
            if(is_dir($file)) {
                $this->recursiveRemoveDirectory($file);
            } else {
                unlink($file);
            }
        }
        rmdir($directory);
    }

    /**
     * Use to get information about an ajax response.
     *
     * @param  bool $status - Status of the response
     * @param  string $message - Message associated with the response
     * @return string - A json array of the response and any errors
     */
    public function ajaxResponse($status,$message) {
        $ajax_return_data = new Collection(); //This will get a status boolean, a message, and an array of errors
        $ajax_return_data->put("status",$status);
        $ajax_return_data->put("error_list",$this->ajax_error_list);
        $ajax_return_data->put("message",$message);

        if($status == true) {
            return response()->json($ajax_return_data,200);
        } else {
            //This is bad, but otherwise it keeps running, maybe there's an alternative?
            return response()->json($ajax_return_data,500)->send() && exit();
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
    public function unlockUsers( ){

        try {
            $users = User::all();
            foreach($users as $user) {
                $user->locked_out = false;
                $user->save();
            }
        } catch(\Exception $e) {
            return response("error",500);
        }
        return response("success",200);
    }

    /**
     * Deletes a stored backup from the installation.
     *
     * @param  Request $request
     * @return string - Json response of the result
     */
    public function delete(Request $request) {
        $this->validate($request,[
            'backup_source'=>'required|in:server',
            'filename'=>'required',
            'backup_type'=>'required|in:system',
            'project_id'=>'required_if:backup_type,project'
        ]);

        $path = env('BASE_PATH')."storage/app/";

        if($request->input("backup_source") == "server") {
            $filename = $path.$request->filename;
            $dir = str_replace(".kora3_backup","",$filename);

            try {
                if($request->input("backup_type") == "system") {
                    if(is_dir($dir)) {
                        $it = new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS);
                        $files = new RecursiveIteratorIterator($it,RecursiveIteratorIterator::CHILD_FIRST);
                        foreach($files as $file) {
                            if($file->isDir()) {
                                rmdir($file->getRealPath());
                            } else {
                                unlink($file->getRealPath());
                            }
                        }
                        rmdir($dir);
                    }
                }
            } catch(\Exception $e) {
                return response()->json(["status"=>false,"message"=>"$e->getMessage()"]);
            }

            return response()->json(["status"=>true,"message"=>$filename]);
        } else {
            flash()->overlay(trans('controller_backup.badrestore'),trans('controller_backup.whoops'));
            return redirect()->back();
        }
    }
}



