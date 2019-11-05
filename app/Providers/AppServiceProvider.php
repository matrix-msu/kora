<?php namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;

class AppServiceProvider extends ServiceProvider {

	/**
	 * Bootstrap any application services.
	 *
	 * @return void
	 */
	public function boot()
	{
		Route::singularResourceParameters(false);

		// the following was taken from https://stackoverflow.com/questions/34099777/laravel-5-1-validation-rule-alpha-cannot-take-whitespace
		Validator::extend('alpha_dash_spaces', function ($attribute, $value) {

			// This will accept alpha, spaces, and hyphens: /^[\pL\s-]+$/u
			// If you do not want to accept hyphens use: /^[\pL\s]+$/u.
			// need regex for alpha, spaces, hyphens, underscores, numeric chars: /^[\w\s-_]+$/u
			return preg_match('/^[\w\s-_]+$/u', $value); 
		});
		// end stackoverflow copy/paste
	}

	/**
	 * Register any application services.
	 *
	 * This service provider is a great spot to register your various container
	 * bindings with the application. As you can see, we are registering our
	 * "Registrar" implementation here. You can add your own bindings too!
	 *
	 * @return void
	 */
	public function register()
	{
        $this->app['url']->forceScheme('https');
        
		$this->app->bind(
			'Illuminate\Contracts\Auth\Registrar',
			'App\Services\Registrar'
		);
	}

}
