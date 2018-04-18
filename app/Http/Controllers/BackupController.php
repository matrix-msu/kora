<?php namespace App\Http\Controllers;

use App\Commands\RestoreTable;
use App\Commands\SaveUsersTable;
use App\User;
use Illuminate\Http\JsonResponse;
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

        //Custom middleware for handling root user checks
        $this->middleware(function ($request, $next) {
            if (Auth::check())
                if (Auth::user()->id != 1)
                    return redirect("/projects")->with('k3_global_error', 'not_admin')->send();

            return $next($request);
        });
    }

    /**
     * Gets list of backups and returns view of the main backup page.
     *
     * @param  Request $request
     * @return View
     */
    public function index(Request $request) {
        $available_backups = array();
        foreach(new \DirectoryIterator(config('app.base_path')."storage/app/".$this->BACKUP_DIRECTORY."/") as $dir) {
            $name = $dir->getFilename();
            if(strpos($name, 'fileRestore') !== false)
                continue;
            if($name!='.' && $name!='..' && $dir->isDir()) {
                if(file_exists(config('app.base_path')."storage/app/".$this->BACKUP_DIRECTORY.'/'.$name.'/.kora3_backup'))
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
        $this->backupSupport($request);

        $order = app('request')->input('order') === null ? 'nod' : app('request')->input('order');
        $order_type = substr($order, 0, 2) === "no" ? "timestamp" : "name";
        $order_direction = substr($order, 2, 3) === "a" ? "asc" : "desc";

        if($order_direction == 'asc') {
            $saved_backups = $saved_backups->sortBy(function ($item) use ($order_type) {
                return $item->get($order_type);
            });
        } else {
            $saved_backups = $saved_backups->sortByDesc(function ($item) use ($order_type) {
                return $item->get($order_type);
            });
        }

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
            'backupLabel'=>'required|alpha_dash',
        ]);
        $backupLabel = $request->backupLabel.'___'.Carbon::now()->toDateTimeString();

        $metadata = isset($request->backupData) ? true : false;
        $files = isset($request->backupFiles) ? true : false;

        //We store this to know if we auto download backup file after backup
        $autoDownload = isset($request->backupDownload) ? true : false;
        $request->session()->put('backup_autodownload', $autoDownload);

        return view('backups.backup',compact('backupLabel','metadata','files'));
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

        $backupName = $request->backupLabel;

        $backupFilepath = $this->BACKUP_DIRECTORY."/".$backupName;
        //Get an instance of Flysystem disk, to use Amazon AWS, SFTP, or Dropbox, change this!
        $backup_fs = Storage::disk('local');
        $backup_disk = "local";
        //
        $backup_fs->makeDirectory($backupFilepath);
        $this->saveDatabase($backup_disk, $backupFilepath);
    }

    /**
     * Loads and executes the background save command for each table.
     *
     * @param  string $backupDisk - Back up file system type
     * @param  string $path - Path where JSON Outputs will be stored
     */
    public function saveDatabase($backupDisk, $path) {
        ini_set('max_execution_time',0);
        Log::info("Backup fp: ".$path);
        $backup_id = DB::table('backup_overall_progress')->insertGetId(['progress'=>0,'overall'=>0,'start'=>Carbon::now(),'created_at'=>Carbon::now(),'updated_at'=>Carbon::now()]);

        $jobs = [new SaveUsersTable($backupDisk, $path, $backup_id)];

        $ac = new AdminController();
        foreach($ac->DATA_TABLES as $table) {
            $backup = "App\Commands\\".$table["backup"];
            $job = new $backup($backupDisk, $path, $backup_id);
            $job->handle();
            //array_push($jobs, new $backup($backupDisk, $path, $backup_id));
        }

//        foreach($jobs as $job) {
//            dispatch($job->onQueue('backup'));
//        }
//
//        Artisan::call('queue:listen', [
//            '--queue' => 'backup',
//            '--timeout' => 1800
//        ]);
    }

    /**
     * Checks the overall progress of the backup.
     *
     * @return string - A json array of the overall progress parts
     */
    public function checkProgress() {
        //Total number of tables commands being saved
        $overall = DB::table('backup_overall_progress')->where('created_at',DB::table('backup_overall_progress')->max('created_at'))->first();
        //Number completed so far
        $partial = DB::table('backup_partial_progress')->where('backup_id',$overall->id)->get();

        return response()->json(["status"=>true,"message"=>"backup_progress","overall"=>$overall,"partial"=>$partial],200);
    }

    /**
     * After progress is complete, run this function to backup files and save the master backup file
     *
     * @param  Request $request
     * @return string - Returns string on error
     */
    public function finishBackup(Request $request) {
        ini_set('max_execution_time',0);
        $label = $request->backupLabel;
        $labelParts = explode('___',$label);
        $name = $labelParts[0];
        $time = $labelParts[1];

        //time to move the files
        $filepath = config('app.base_path')."storage/app/files/";
        $newfilepath = config('app.base_path')."storage/app/".$this->BACKUP_DIRECTORY."/".$label."/files/";
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
        $path = config('app.base_path')."storage/app/".$this->BACKUP_DIRECTORY."/".$label."/";
        $data['kora3'] = $k3;
        $json = json_encode($data);
        $newfile = $path . ".kora3_backup";

        $bytes_written = File::put($newfile, $json);
        if ($bytes_written === false)
            return response()->json(["status"=>false,"message"=>"backup_file_failed"],500);

        $this->unlockUsers();

        $totalSize = $this->humanFileSize($this->getDirectorySize($path));

        return response()->json(["status"=>true,"message"=>"backup_finished","totalSize"=>$totalSize],200);
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
     */
    public function download($path) {
        $fullpath = config('app.base_path')."storage/app/".$this->BACKUP_DIRECTORY."/".$path."/";

        $zipname = $path.'.zip';
        $zipdir = config('app.base_path')."storage/app/".$this->BACKUP_DIRECTORY."/";
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
                    $filepath = config('app.base_path')."storage/app/".$filename;
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
                            return redirect()->with('k3_global_error', 'restore_extract_failed')->back();
                        }
                    } catch(\Exception $e) {
                        flash()->overlay("The file could not be moved to the backup directory.","Whoops");
                        return redirect()->with('k3_global_error', 'restore_file_unmovable')->back();
                    }
                } else {
                    flash()->overlay("There is something wrong with the file that was uploaded","Whoops");
                    return redirect()->with('k3_global_error', 'restore_file_failed')->back();
                }
            } else {
                flash()->overlay("No file was uploaded.","Whoops");
                return redirect()->with('k3_global_error', 'restore_file_missing')->back();
            }
        } else {
            return redirect()->with('k3_global_error', 'backup_no_source')->back();
        }

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
        $dir = config('app.base_path').'storage/app/'.$request->filename;

        //Delete all existing data
        try {
            $ac = new AdminController();
            $ac->deleteData();
        } catch(\Exception $e) {
            return response()->json(["status"=>false,"message"=>"restore_dbwipe_fail"],500);
        }

        //Delete the files directory
        if(file_exists(config('app.base_path')."storage/app/files/")) //this check is to see if it was deleted in a failed restore
            $this->recursiveRemoveDirectory(config('app.base_path')."storage/app/files/");

        //NEW PROCESS For restore using jobs
        ini_set('max_execution_time',0);
        Log::info("Restore in progress...");
        $restore_id = DB::table('restore_overall_progress')->insertGetId(['progress'=>0,'overall'=>0,'start'=>Carbon::now(),'created_at'=>Carbon::now(),'updated_at'=>Carbon::now()]);
        //These jobs need restore versions. Will test with TEXT

        $jobs = [new RestoreTable("users",$dir, $restore_id)];

        $ac = new AdminController();
        foreach($ac->DATA_TABLES as $table)
            array_push($jobs, new RestoreTable($table["name"],$dir, $restore_id));

        foreach($jobs as $job) {
            dispatch($job->onQueue('restore'));
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

        return response()->json(["status"=>true,"message"=>"restore_progress","overall"=>$overall,"partial"=>$partial],200);
    }

    /**
     * After progress is complete, run this function to backup files and save the master backup file.
     *
     * @param  Request $request
     */
    public function finishRestore(Request $request) {
        $filepath = config('app.base_path').'storage/app/'.$request->filename.'/files/';
        $newfilepath = config('app.base_path')."storage/app/files/";

        //time to move the files
        if(!file_exists($newfilepath))
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
            if(is_dir($file))
                $this->recursiveRemoveDirectory($file);
            else
                unlink($file);
        }
        rmdir($directory);
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
    public function unlockUsers(){
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

    /**
     * Deletes a stored backup from the installation.
     *
     * @param  Request $request
     * @return JsonResponse - Json response of the result
     */
    public function delete(Request $request) {
        $this->validate($request,[
            'backup_source'=>'required|in:server',
            'filename'=>'required',
            'backup_type'=>'required|in:system',
            'project_id'=>'required_if:backup_type,project'
        ]);

        $path = config('app.base_path')."storage/app/";

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
                return response()->json(["status"=>false,"message"=>"backup_delete_failed"],500);
            }

            return response()->json(["status"=>true,"message"=>"backup_delete_success"],200);
        } else {
            return response()->json(["status"=>false,"message"=>"backup_delete_invalid"],500);
        }
    }

    /**
     * Stores extraneous backup information for statistics purposes
     *
     * @param  Request $request
     */
    private function backupSupport($request) {
        try {
            $user_support = DB::table('backup_support')->where('user_id', Auth::user()->id)->where('view', 'backups.index')->first();
            if($user_support === null) {
                DB::table('backup_support')->insert(['user_id' => Auth::user()->id, 'view' => 'backups.index', 'hasRun' => Carbon::now(), 'accessed' => 0, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
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
        } catch(\Exception $e) {
            $user_support = null;
        }
    }
}



