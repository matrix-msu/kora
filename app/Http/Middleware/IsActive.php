<?php namespace App\Http\Middleware;

use Closure;

class IsActive {



	/**
	 * Handles the user activation.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
        if (!(\Auth::user()->active))
        {
            flash()->overlay('You must activate your account to view that page.', 'Whoops.');

            return redirect('user/activate');
        }

        return $next($request);
    }
}
