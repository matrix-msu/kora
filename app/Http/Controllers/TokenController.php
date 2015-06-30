<?php namespace App\Http\Controllers;

use App\Token;
use App\Project;
use App\Http\Requests;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


class TokenController extends Controller {

    /**
     * User must be logged in to access views in this controller.
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('active');
    }

    /**
     * @return Response
     */
    public function index()
    {
        $tokens = Token::all();
        $projects = Project::lists('name', 'pid');
        $all_projects = Project::all(); //Second variable created here to get around weird indexing needed for pivot table in $projects

        return view('tokens.index', compact('tokens', 'projects', 'all_projects'));
    }

    /**
     * Creates new token of certain type and assigns projects.
     *
     * @param Request $request
     * @return Response
     */
    public function create(Request $request)
    {
        $instance = new Token();
        $instance->token = TokenController::tokenGen();
        $instance->type = $request['type'];
        $instance->save();

        if (!is_null($request['projects']))
            $instance->projects()->attach($request['projects']);

        return redirect('tokens');
    }

    /**
     * Detaches a project from a token.
     *
     * @param Request $request
     */
    public function deleteProject(Request $request)
    {
        $instance = TokenController::getToken($request->token);
        $instance->projects()->detach($request['pid']);
    }

    /**
     * Attaches a project to a token.
     *
     * @param Request $request
     */
    public function addProject(Request $request)
    {
        $instance = TokenController::getToken($request->token);
        $instance->projects()->attach($request['pid']);
    }

    public function deleteToken(Request $request)
    {
        $instance = TokenController::getToken($request->id);
        $instance->delete();

        flash()->overlay('Your token has been deleted', 'Success!');
    }

    /**
     * Creates random string of alphanumeric characters.
     *
     * @return string
     */
    public static function tokenGen()
    {
        $valid = 'abcdefghijklmnopqrstuvwxyz';
        $valid .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $valid .= '0123456789';

        $token = '';
        for ($i = 0; $i < 24; $i++){
            $token .= $valid[( rand() % 62 )];
        }
        return $token;
    }

    /**
     * @param $id
     * @return Token
     */
    public static function getToken($id)
    {
        $token = Token::where('id', '=', $id)->first();
        return $token;
    }
}
