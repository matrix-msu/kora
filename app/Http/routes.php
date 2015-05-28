<?php

Route::get('/', 'WelcomeController@index');

//project routes
Route::resource('projects', 'ProjectController');

//form routes
Route::get('/projects/{id}/forms','ProjectController@show'); //alias for project/{id}
Route::patch('/projects/{pid}/forms/{fid}','FormController@update');
Route::get('/projects/{pid}/forms/create','FormController@create');
Route::get('/projects/{pid}/forms/{fid}','FormController@show');
Route::delete('/projects/{pid}/forms/{fid}','FormController@destroy');
Route::get('/projects/{pid}/forms/{fid}/edit','FormController@edit');
Route::post('/projects/{pid}','FormController@store');

//field routes
Route::get('/projects/{id}/forms/{fid}/fields','FormController@show'); //alias for project/{id}
Route::patch('/projects/{pid}/forms/{fid}/fields/{flid}','FieldController@update');
Route::get('/projects/{pid}/forms/{fid}/fields/create','FieldController@create');
Route::get('/projects/{pid}/forms/{fid}/fields/{flid}','FieldController@show');
Route::delete('/projects/{pid}/forms/{fid}/fields/{flid}','FieldController@destroy');
Route::get('/projects/{pid}/forms/{fid}/fields/{flid}/edit','FieldController@edit');
Route::patch('/projects/{pid}/forms/{fid}/fields/{flid}/required','FieldController@updateRequired');
Route::patch('/projects/{pid}/forms/{fid}/fields/{flid}/default','FieldController@updateDefault');
Route::get('/projects/{pid}/forms/{fid}/fields/{flid}/options','FieldController@show'); //alias for fields/{id}
Route::patch('/projects/{pid}/forms/{fid}/fields/{flid}/options/update','FieldController@updateUpdates');
Route::post('/projects/{pid}/forms/{fid}','FieldController@store');

//user routes
Route::resource('user', 'Auth\UserController@index');

Route::controllers([
	'auth' => 'Auth\AuthController',
	'password' => 'Auth\PasswordController',
]);