<?php namespace App\Http\Controllers;

use App\User;
use App\Group;
use App\Http\Requests;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class GroupController extends Controller {

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
     * @return Response
     */
    public function index()
    {
        $groups = Group::all();
        $users = User::lists('username', 'id');
        $all_users = User::all();
        return view('groups.index', compact('groups', 'users', 'all_users'));
    }

    /**
     * @param Request $request
     * @return Reposne
     */
    public function create(Request $request)
    {
        $name = $request['name'];
        $users = $request['users'];

        if($name == ""){
            flash()->overlay('You must enter a name for the group.', 'Whoops.');
            return redirect('/groups');
        }
        $instance = new Group();
        $instance->name = $name;
        $instance->save();

        if(!is_null($users))
            $instance->users()->attach($users);

        flash()->overlay('Group "'.$name.'" has been created.', 'Success!');
        return redirect('/groups');
    }

    /**
     * Adds a user to a group.
     *
     * @param Request $request
     */
    public function addUser(Request $request)
    {
        $instance = Group::where('id', '=', $request['group'])->first();
        $instance->users()->attach($request['user']);
    }

    /**
     * Removes a user from a group.
     *
     * @param Request $request
     */
    public function removeUser(Request $request)
    {
        $instance = Group::where('id', '=', $request['group'])->first();
        $instance->users()->detach($request['user']);
    }

    public function deleteGroup(Request $request)
    {
        $instance = Group::where('id', '=', $request['group'])->first();
        $instance->delete();

        flash()->overlay('Group has been deleted.', 'Success!');
    }

}
