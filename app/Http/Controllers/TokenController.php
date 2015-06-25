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
        $instance = Token::where('id','=',$request->token)->first();
        $instance->projects()->detach($request['pid']);
    }

    /**
     * Attaches a project to a token.
     *
     * @param Request $request
     */
    public function addProject(Request $request)
    {
        $instance = Token::where('id','=',$request->token)->first();
        $instance->projects()->attach($request['pid']);
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
