<?php

define('LARAVEL_START', microtime(true));

/*
|--------------------------------------------------------------------------
| Register The Composer Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader
| for our application. We just need to utilize it! We'll require it
| into the script here so that we do not have to worry about the
| loading of any our classes "manually". Feels great to relax.
|
*/

/**
 * Generate a url for the application. This override for Foundation::helpers.php needs to be created before the vendor
 * files are loaded. The goal of the override is to always force HTTPS
 *
 * @param  string|null  $path
 * @param  mixed  $parameters
 * @param  bool|null  $secure
 * @return \Illuminate\Contracts\Routing\UrlGenerator|string
 */
function url($path = null, $parameters = [], $secure = null)
{
    if (is_null($path)) {
        return app(\Illuminate\Contracts\Routing\UrlGenerator::class);
    }

    return app(\Illuminate\Contracts\Routing\UrlGenerator::class)->to($path, $parameters, true);
}

/**
 * Get an instance of the redirector. This override for Foundation::helpers.php needs to be created before the vendor
 * files are loaded. The goal of the override is to always force HTTPS
 *
 * @param  string|null  $to
 * @param  int  $status
 * @param  array  $headers
 * @param  bool|null  $secure
 * @return \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse
 */
function redirect($to = null, $status = 302, $headers = [], $secure = null)
{
    if (is_null($to)) {
        return app('redirect');
    }

    return app('redirect')->to($to, $status, $headers, true);
}

require __DIR__.'/../vendor/autoload.php';

/*
|--------------------------------------------------------------------------
| Include The Compiled Class File
|--------------------------------------------------------------------------
|
| To dramatically increase your application's performance, you may use a
| compiled class file which contains all of the classes commonly used
| by a request. The Artisan "optimize" is used to create this file.
|
*/

$compiledPath = __DIR__.'/cache/compiled.php';

if (file_exists($compiledPath))
{
	require $compiledPath;
}
