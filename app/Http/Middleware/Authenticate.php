<?php namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;

class Authenticate {

    /*
    |--------------------------------------------------------------------------
    | Authenticate
    |--------------------------------------------------------------------------
    |
    | This middleware handles the authentication of a user
    |
    */

	/**
	 * @var Guard - The Guard implementation
	 */
	protected $auth;

	/**
	 * Create a new filter instance.
	 *
	 * @param  Guard $auth - The guard implementation to assign
	 */
	public function __construct(Guard $auth) {
		$this->auth = $auth;
	}

	/**
	 * Handle an incoming request.
	 *
	 * @param  Request $request
	 * @param  Closure $next
	 * @return mixed
	 */
	public function handle($request, Closure $next) {
		if($this->auth->guest())  {
			if($request->ajax()) {
				return response("Unauthorized.", 401);
			} else {
				return redirect()->guest('/');
			}
		}

        return $next($request);
	}

}
