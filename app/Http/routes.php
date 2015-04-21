<?php

Route::get('/', 'WelcomeController@index');

//project routes
Route::resource('projects', 'ProjectController');

//form routes
Route::get('/projects/{id}/forms','ProjectController@show'); //alias for project/{id}
Route::patch('/projects/{pid}/forms','FormController@update'); //alias required for submitting of form edits
Route::get('/projects/{pid}/forms/create','FormController@create');
Route::get('/projects/{pid}/forms/{fid}','FormController@show');
Route::delete('/projects/{pid}/forms/{fid}','FormController@destroy');
Route::get('/projects/{pid}/forms/{fid}/edit','FormController@edit');
Route::post('/projects/{pid}','FormController@store');

//user routes
Route::resource('user', 'Auth\UserController@index');

Route::controllers([
	'auth' => 'Auth\AuthController',
	'password' => 'Auth\PasswordController',
]);