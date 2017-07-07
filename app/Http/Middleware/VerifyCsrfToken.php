<?php namespace App\Http\Middleware;

use App\Http\Requests\Request;
use Closure;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifier;
use Illuminate\Session\TokenMismatchException;

class VerifyCsrfToken extends BaseVerifier {

    /*
    |--------------------------------------------------------------------------
    | Verify Csrf Token
    |--------------------------------------------------------------------------
    |
    | This middleware handles authentication of request tokens for POST routes
    |
    */

    /**
     * @var array - Determines what POST routes are publicly accessible
     */
    protected $except_urls = [
        'api/version',
        'api/search',
        'api/delete',
        'api/create',
        'api/edit',
        'api/projects/{pid}/forms',
        'api/projects/{pid}/forms/{fid}/fields',
        'api/projects/{pid}/forms/{fid}/recordCount',
    ];

	/**
	 * Handle an incoming request.
	 *
	 * @param  Request  $request
	 * @param  Closure  $next
	 * @return mixed
	 */
    public function handle($request, Closure $next) {
        $regex = '#' . implode('|', $this->except_urls) . '#';

        if($this->isReading($request) || $this->tokensMatch($request) || preg_match($regex, $request->path())) {
            return $this->addCookieToResponse($request, $next($request));
        }

        throw new TokenMismatchException;
    }

}
