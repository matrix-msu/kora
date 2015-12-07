<?php namespace App\Http\Controllers;

use App\Version;
use App\Script;
use App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class UpdateController extends Controller {

    /*************************************************************
     *************************************************************
     *************************************************************
     *************************************************************
     ********This should be examined for safety before************
     *************regarded as working code...*********************
     *************************************************************
     *************************************************************
     *************************************************************
     *************************************************************/


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
    public function getCurrentVersion()
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
    public function gitUpdate()
    {
        //
        // Pull an update from git.
        // At this point, it has been established that using
        // exec is safe because the user's environment has git enabled.
        //
        exec("git pull");

        //
        // Make new entries in the scripts table for
        // those that do not exist yet (ignores '.' and '..')
        //
        $scriptNames = array_diff(scandir(env('BASE_PATH'). DIRECTORY_SEPARATOR ."scripts"), array('..', '.'));
        foreach($scriptNames as $scriptName)
        {
            if (is_null(Script::where('name', '=', $scriptName)))
            {
                $script = new Script();
                $script->filename = $scriptName;
                $script->save();
            }
        }

        //
        // Run scripts that have not yet been run.
        //
        foreach(Script::all() as $script)
        {
            if(!$script->hasRun)
            {
                $includeString = env('BASE_PATH'). DIRECTORY_SEPARATOR .'scripts'. DIRECTORY_SEPARATOR . $script->filename.".php";
                include $includeString;
                $script->hasRun = true;
                $script->save();
            }
        }

        UpdateController::storeVersion();
        UpdateController::refresh();
    }

    /**
     * Updates the application independent of laravel and git.
     */
    public function independentUpdate()
    {

        UpdateController::storeVersion();
        UpdateController::refresh();
    }

    /**
     * Clears the cached views the Laravel compiled caches.
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
}
