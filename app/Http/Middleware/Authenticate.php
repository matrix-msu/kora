<?php namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

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
                //Replicating guest function to force https in production
                //This fixes the issue where user wasn't bounced to intended url after login in https
                $secure = false;
                if(config('app.env') === 'production')
                    $secure = true;
                Session::put('url.intended', redirect()->getUrlGenerator()->current());

                return redirect()->to('/', 302, [], $secure);
			}
		}

        return $next($request);
	}

}
