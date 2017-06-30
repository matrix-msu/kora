<?php namespace App\Http\Controllers;

use App\Token;
use App\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class TokenController extends Controller {

    /*
    |--------------------------------------------------------------------------
    | Token Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles creation and management of data authentication tokens
    |
    */

    /**
     * Constructs controller and makes sure user is authenticated and a system admin.
     */
    public function __construct() {
        $this->middleware('auth');
        $this->middleware('active');
        $this->middleware('admin');
    }

    /**
     * Gets the view for the token management page.
     *
     * @return View
     */
    public function index() {
        $tokens = Token::all();
        $projects = Project::lists('name', 'pid')->all();
        $all_projects = Project::all(); //Second variable created here to get around weird indexing needed for pivot table in $projects

        return view('tokens.index', compact('tokens', 'projects', 'all_projects'));
    }

    /**
     * Creates a new token.
     *
     * @param  Request $request
     * @return Redirect
     */
    public function create(Request $request)
    {
        $instance = new Token();
        $instance->token = self::tokenGen();
        $instance->title = $request['title'];
        $instance->search = isset($request['search']) ? true : false;
        $instance->create = isset($request['create']) ? true : false;
        $instance->edit = isset($request['edit']) ? true : false;
        $instance->delete = isset($request['delete']) ? true : false;
        $instance->save();

        if (!is_null($request['projects']))
            $instance->projects()->attach($request['projects']);

        return redirect('tokens');
    }

    /**
     * Removes project authentication from a token.
     *
     * @param  Request $request
     */
    public function deleteProject(Request $request) {
        $instance = self::getToken($request->token);
        $instance->projects()->detach($request['pid']);
    }

    /**
     * Adds project authentication from a token.
     *
     * @param  Request $request
     */
    public function addProject(Request $request) {
        $instance = self::getToken($request->token);
        $instance->projects()->attach($request['pid']);
    }

    /**
     * Deletes a token from Kora3.
     *
     * @param  Request $request
     */
    public function deleteToken(Request $request) {
        $instance = self::getToken($request->id);
        $instance->delete();

        flash()->overlay(trans('controller_token.delete'), trans('controller_token.success'));
    }

    /**
     * Generates a new 24 character token.
     *
     * @return string - Newly created token
     */
    public static function tokenGen() {
        $valid = 'abcdefghijklmnopqrstuvwxyz';
        $valid .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $valid .= '0123456789';

        $token = '';
        for($i = 0; $i < 24; $i++) {
            $token .= $valid[( rand() % 62 )];
        }
        return $token;
    }

    /**
     * Gets a token based on ID.
     *
     * @param  int $id - Token ID
     * @return Token - Requested token
     */
    public static function getToken($id) {
        return Token::where('id', '=', $id)->first();
    }
}
