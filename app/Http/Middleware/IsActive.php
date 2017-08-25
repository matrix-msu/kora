<?php namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class IsActive {

    /*
    |--------------------------------------------------------------------------
    | Is Active
    |--------------------------------------------------------------------------
    |
    | This middleware handles checking if current user is active
    |
    */

	/**
	 * Handles the user activation check.
	 *
	 * @param  Request $request
	 * @param  Closure $next
	 * @return mixed
	 */
	public function handle($request, Closure $next) {
        if(!(\Auth::user()->active))
            return redirect('/')->with('k3_global_error', 'user_not_activated');

        if(\Auth::user()->locked_out) { //This is for backup and restore operations, see BackupController@lockUsers
            \Auth::logout();
            return redirect('/')->with('k3_global_error', 'user_locked_out');
        }

        return $next($request);
    }
}
