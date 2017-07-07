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
        if(!(\Auth::user()->active)) {
            flash()->overlay(trans('middleware_isactive.email'), trans('middleware_isactive.whoops'));
            return redirect('/');
        }

        if(\Auth::user()->locked_out) { //This is for backup and restore operations, see BackupController@lockUsers
            flash()->overlay(trans('middleware_isactive.locked'),trans('middleware_isactive.whoops'));
            \Auth::logout();
            return redirect('/');
        }

        return $next($request);
    }
}
