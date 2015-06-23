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

        return view('tokens.index', compact('tokens'), compact('projects'));
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
}
