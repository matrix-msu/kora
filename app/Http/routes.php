<?php

Route::get('/', 'WelcomeController@index');

Route::resource('projects', 'ProjectController');
Route::resource('user', 'Auth\UserController@index');

Route::controllers([
	'auth' => 'Auth\AuthController',
	'password' => 'Auth\PasswordController',
]);