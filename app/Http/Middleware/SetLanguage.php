<?php namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use \Illuminate\Support\Facades\App;
use \Illuminate\Support\Facades\Config;

class SetLanguage {

    /*
    |--------------------------------------------------------------------------
    | Set Language
    |--------------------------------------------------------------------------
    |
    | This middleware handles setting the appropriate language for the user
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
     * @param  Request  $request
     * @param  Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next) {
        $lang = $request->session()->get('language');
        $langs_available = Config::get('app.locales_supported');

        if($this->auth->check()) { //if user is signed in, their preferences are used
            $lang = $this->auth->user()->language;
        } else {
            $guest_lang = ($request->session()->get('guest_user_language'));
            if($guest_lang === null || $guest_lang == "") { //Do they previously set a language this session?
                $request->session()->put("guest_user_language","en");
                $lang = "en";
            } else if(ctype_digit($guest_lang)) { //If $guest_lang is numerical then get the correct string
                $lang= $langs_available->get($guest_lang)[0];
            } else {
                $lang = $guest_lang;
            }
        }

        App::setLocale($lang);

        return $next($request);
    }

}
