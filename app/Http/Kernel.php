<?php namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel {

	/**
	 * The application's global HTTP middleware stack.
     * NOTE: This seems to be deprecated in favor of middlewareGroups below. Leaving here just in case.
	 *
	 * @var array
	 */
//	protected $middleware = [
//		'Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode',
//		'Illuminate\Cookie\Middleware\EncryptCookies',
//		'Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse',
//        \Illuminate\Routing\Middleware\SubstituteBindings::class,
//		'Illuminate\Session\Middleware\StartSession',
//		'Illuminate\View\Middleware\ShareErrorsFromSession',
//		'App\Http\Middleware\VerifyCsrfToken',
//        'language' => 'App\Http\Middleware\SetLanguage'
//	];

    protected $middlewareGroups = [
        'web' => [
            \Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            'language' => \App\Http\Middleware\SetLanguage::class,
        ],
        'api' => [
            'throttle:60,1',
            'bindings',
        ],
    ];

	/**
	 * The application's route middleware.
	 *
	 * @var array
	 */
	protected $routeMiddleware = [
		'auth' => \App\Http\Middleware\Authenticate::class,
		'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'bindings' => \Illuminate\Routing\Middleware\SubstituteBindings::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'active' => \App\Http\Middleware\IsActive::class,
        'admin' => \App\Http\Middleware\IsAdmin::class,
        'install' => \App\Http\Middleware\IsInstalled::class,
	];

}
