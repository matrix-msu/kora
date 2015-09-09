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
            flash()->overlay('You must activate your account. Check your email.', 'Whoops.');
            return redirect('/');
        }

        if(\Auth::user()->locked_out){ //This is for backup and restore operations, see BackupController@lockUsers
            flash()->overlay("You are temporarily locked out during system maintenance.  Wait a few minutes and
            try again, your account will be unlocked soon.",'Whoops.');
            \Auth::logout();
            return redirect('/');
        }

        return $next($request);
    }
}
