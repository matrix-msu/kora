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
                $request = redirect()->getUrlGenerator()->getRequest();

                $intended = $request->method() === 'GET' && $request->route() && ! $request->expectsJson()
                    ? redirect()->getUrlGenerator()->full()
                    : redirect()->getUrlGenerator()->previous();

                //Force https
                $intended = str_replace('http://','https://',$intended);

                if($intended) {
                    redirect()->setIntendedUrl($intended);
                }

                return redirect()->to('/home', 302, [], true);
			}
		}

        return $next($request);
	}

}
