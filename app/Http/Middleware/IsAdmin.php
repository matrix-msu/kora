<?php namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class IsAdmin {

    /*
    |--------------------------------------------------------------------------
    | Is Admin
    |--------------------------------------------------------------------------
    |
    | This middleware handles checking if current user is an admin
    |
    */

	/**
	 * Handle an incoming request.
	 *
	 * @param  Request  $request
	 * @param  Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next) {
        if(!(\Auth::user()->admin))
            return redirect('/')->with('k3_global_error', 'not_admin');

        return $next($request);
    }
}
