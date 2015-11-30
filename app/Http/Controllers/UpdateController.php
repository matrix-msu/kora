<?php namespace App\Http\Controllers;

use App\Version;
use App\Http\Requests;
use Illuminate\Http\Request;
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
        $git = is_dir( env('BASE_PATH').'/.git');

        //Determine if an update is needed (this is determined independent of how Kora was acquired)
        $update = UpdateController::checkVersion();

        dd($update);

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


        //
        // Get the html of the github page, then find the current version.
        //
        $search = "Current Kora Version: ";
        $html = file_get_contents('http://matrix-msu.github.io/Kora3/');

        $pos = strpos($html, $search) + strlen($search);
        $sub = substr($html, $pos);
        $pos = strpos($sub, "<");

        //Current version of Kora 3
        $currentVersion = substr($sub, 0, $pos);

        return version_compare($currentVersion, $thisVersion, ">");
    }

    /**
     * Updates the application using the git update routine.
     */
    public function gitUpdate()
    {

    }

    /**
     * Updates the application independent of laravel and git.
     */
    public function independentUpdate()
    {

    }

}
