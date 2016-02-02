<?php namespace App\Http\Middleware;

use Closure;

class IsAdmin {

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
        if (!(\Auth::user()->admin))
        {
            flash()->overlay(trans('middleware_isadmin.admin'), trans('middleware_isadmin.whoops'));
            return redirect('/');
        }

        return $next($request);
    }
}
