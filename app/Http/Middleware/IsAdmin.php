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
            flash()->overlay('You must be an admin to view that page.', 'Whoops.');
            return redirect('/');
        }

        return $next($request);
    }
}
