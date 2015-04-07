<?php

Route::get('/', 'WelcomeController@index');

Route::resource('projects', 'ProjectController');

Route::controllers([
	'auth' => 'Auth\AuthController',
	'password' => 'Auth\PasswordController',
]);