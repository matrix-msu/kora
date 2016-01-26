<?php namespace App\Http\Controllers;

use App\Version;
use App\Script;
use App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class UpdateController extends Controller {

    /**
     * User must be logged in and admin to access views in this controller.
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('active');
        $this->middleware('admin');
    }

    /**
     * Update index page.
     */
    public function index()
    {
        //Determine if the user installed Kora 3 using Git (.git directory exists)
        $git = is_dir( env('BASE_PATH'). DIRECTORY_SEPARATOR . '.git');

        //Determine if an update is needed (this is determined independent of how Kora was acquired).
        $update = UpdateController::checkVersion();

        return view('update.index', compact('git', 'update'));
    }

    /**
     * Checks if this Kora 3 version is equal to the current Kora 3 version.
     * False if updated not needed, true if updated needed.
     * \return bool Depending on the versions.
     */
    public function checkVersion()
    {
        //Version of this Kora 3
        $thisVersion = DB::table('versions')->orderBy('created_at', 'desc')->first()->version;

        //Current version of Kora 3
        $currentVersion = UpdateController::getCurrentVersion();

        return version_compare($currentVersion, $thisVersion, ">");
    }

    /**
     * Gets the current version of Kora as a string.
     * \return string The standardized version string.
     */
    static public function getCurrentVersion()
    {
        //
        // Get the html of the github page, then find the current version in the html.
        //
        $search = "Current Kora Version: ";
        $html = file_get_contents('http://matrix-msu.github.io/Kora3/');

        $pos = strpos($html, $search) + strlen($search); //Position of the version string.
        $sub = substr($html, $pos);
        $pos = strpos($sub, "<");

        //Current version of Kora 3
        return substr($sub, 0, $pos);
    }


    /**
     * Updates the application using the git update routine.
     */
    public function runScripts()
    {
        // Allow the script to run for 20 minutes.
        ignore_user_abort(true);
        set_time_limit(1200);

        //
        // Make new entries in the scripts table for
        // those that do not exist yet (ignores '.' and '..')
        //
        $scriptNames = array_diff(scandir(env('BASE_PATH'). "scripts"), array('..', '.'));
        foreach($scriptNames as $scriptName)
        {
            if (is_null(Script::where('filename', '=', $scriptName)->first()))
            {
                $script = new Script();
                $script->hasRun = false;
                $script->filename = $scriptName;
                $script->save();
            }
        }

        if (UpdateController::hasPulled())
        {
            //
            // Run scripts that have not yet been run.
            //
            foreach (Script::all() as $script) {
                if (!$script->hasRun) {
                    $includeString = env('BASE_PATH') . 'scripts' . DIRECTORY_SEPARATOR . $script->filename;
                    include $includeString;
                    $script->hasRun = true;
                    $script->save();
                }
            }
            UpdateController::refresh();
            UpdateController::storeVersion();
        }
        else
        {
            //
            // Inform the user they have not successfully executed a git pull.
            //
            flash()->overlay(trans('controller_update.pullfail'), trans('controller_admin.whoops'));
        }

        ignore_user_abort(false);
        return redirect('update');
    }

    /**
     * Clears the cached views and the Laravel compiled caches.
     */
    private function refresh()
    {
        //
        // Clear cached views.
        //
        $viewsPath = env('BASE_PATH') . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'framework' . DIRECTORY_SEPARATOR . 'views';
        $views = array_diff(scandir($viewsPath), array('..', '.', '.gitignore'));

        foreach($views as $view)
            unlink($viewsPath . DIRECTORY_SEPARATOR . $view);

        //
        // Clear Laravel's caches.
        //
        Artisan::call('clear-compiled');
        Artisan::call('optimize');
    }

    /**
     * Stores the new version of Kora 3 as the current version.
     */
    private function storeVersion()
    {
        $v = new Version();
        $v->version = UpdateController::getCurrentVersion();
        $v->save();
    }

    /**
     * Determine if any new scripts are in the app/scripts directory.
     * This effectively determines if the user has actually done a git pull.
     */
    private function hasPulled()
    {
        foreach(Script::all() as $script)
        {
            if(!$script->hasRun)
            {   // We have found a script that has not run, hence the user has executed a git pull successfully.
                return true;
            }
        }
        // No scripts were found that were not already run, hence the user has not executed a git pull successfully.
        return false;
    }
}
