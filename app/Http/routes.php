<?php

Route::get('/', 'WelcomeController@index');
Route::get('/home', 'WelcomeController@index');
Route::post('/language','WelcomeController@setTemporaryLanguage');
Route::get('/dashboard', 'DashboardController@dashboard');

//api routes
Route::get('/api/version','RestfulController@getKoraVersion');
Route::get('/api/projects/{pid}/forms','RestfulController@getProjectForms');
Route::get('/api/projects/{pid}/forms/{fid}/fields','RestfulController@getFormFields');
Route::get('/api/projects/{pid}/forms/{fid}/recordCount','RestfulController@getFormRecordCount');
Route::post('/api/search','RestfulController@search');
Route::delete('/api/delete','RestfulController@delete');
Route::post('/api/create','RestfulController@create');
Route::put('/api/edit','RestfulController@edit');

//project routes
Route::get('/projects/import','ProjectController@importProjectView');
Route::post('/projects/import','ImportController@importProject');
Route::resource('projects', 'ProjectController');
Route::post('projects/request', 'ProjectController@request');
Route::post('projects/archive', 'ProjectController@request');
Route::post('projects/{pid}/archive', 'ProjectController@setArchiveProject');

//project group routes
Route::get('/projects/{pid}/manage/projectgroups', 'ProjectGroupController@index');
Route::post('/projects/{pid}/manage/projectgroups/create', 'ProjectGroupController@create');
Route::patch('projects/{pid}/manage/projectgroups/removeUser', 'ProjectGroupController@removeUser');
Route::patch('projects/{pid}/manage/projectgroups/addUser', 'ProjectGroupController@addUser');
Route::patch('projects/{pid}/manage/projectgroups/updatePermissions', 'ProjectGroupController@updatePermissions');
Route::patch('projects/{pid}/manage/projectgroups/updateName', 'ProjectGroupController@updateName');
Route::delete('projects/{pid}/manage/projectgroups/deleteProjectGroup', 'ProjectGroupController@deleteProjectGroup');

//form group routes
Route::get('/projects/{pid}/forms/{fid}/manage/formgroups', 'FormGroupController@index');
Route::post('/projects/{pid}/forms/{fid}/manage/formgroups/create', 'FormGroupController@create');
Route::patch('projects/{pid}/forms/{fid}/manage/formgroups/removeUser', 'FormGroupController@removeUser');
Route::patch('projects/{pid}/forms/{fid}/manage/formgroups/addUser', 'FormGroupController@addUser');
Route::patch('projects/{pid}/forms/{fid}/manage/formgroups/updatePermissions', 'FormGroupController@updatePermissions');
Route::patch('projects/{pid}/forms/{fid}/manage/formgroups/updateName', 'FormGroupController@updateName');
Route::delete('projects/{pid}/forms/{fid}/manage/formgroups/deleteFormGroup', 'FormGroupController@deleteFormGroup');

//admin routes
Route::get('/admin/users', 'AdminController@users');
Route::patch('/admin/update', 'AdminController@update');
Route::patch('/admin/batch', 'AdminController@batch');
Route::delete('admin/deleteUser/{id}', 'AdminController@deleteUser');
Route::post('/admin/order66','AdminController@deleteData');

//Kora Exodus routes
Route::get('/exodus', 'ExodusController@index');
Route::post('/exodus/projects', 'ExodusController@getProjectList');
Route::post('/exodus/migrate', 'ExodusController@migrate');
Route::get('/exodus/progress','ExodusController@checkProgress');
Route::post('/exodus/user/unlock','ExodusController@unlockUsers');
Route::post('/exodus/start','ExodusController@startExodus');
Route::post('/exodus/finish','ExodusController@finishExodus'); //

//token routes
Route::get('/tokens', 'TokenController@index');
Route::post('/tokens/create', 'TokenController@create');
Route::patch('/tokens/deleteProject', 'TokenController@deleteProject');
Route::patch('/tokens/addProject', 'TokenController@addProject');
Route::delete('/tokens/deleteToken', 'TokenController@deleteToken');

//plugin routes
Route::get('/plugins', 'PluginController@index');
Route::post('/plugins/install/{name}', 'PluginController@install');
Route::patch('/plugins/update', 'PluginController@update');
Route::post('/plugins/activate', 'PluginController@activate');
Route::delete('/plugins/{plid}', 'PluginController@destroy');
Route::get('/plugins/{name}/loadView/{view}', 'PluginController@loadView');
Route::post('/plugins/{name}/{action}', 'PluginController@action');

//association routes
Route::get('/projects/{pid}/forms/{fid}/assoc', 'AssociationController@index');
Route::post('/projects/{pid}/forms/{fid}/assoc', 'AssociationController@create');
Route::post('/projects/{pid}/forms/{fid}/assoc/request', 'AssociationController@requestAccess');
Route::delete('/projects/{pid}/forms/{fid}/assoc', 'AssociationController@destroy');

//form routes
Route::get('/projects/{pid}/forms','ProjectController@show'); //alias for project/{id}
Route::patch('/projects/{pid}/forms/{fid}','FormController@update');
Route::get('/projects/{pid}/forms/create','FormController@create');
Route::get('/projects/{pid}/forms/import','FormController@importFormView');
Route::get('/projects/{pid}/forms/importk2','FormController@importFormViewK2');
Route::post('/projects/{pid}/forms/import','ImportController@importForm');
Route::post('/projects/{pid}/forms/importk2','ImportController@importFormK2');
Route::get('/projects/{pid}/forms/{fid}','FormController@show');
Route::delete('/projects/{pid}/forms/{fid}','FormController@destroy');
Route::get('/projects/{pid}/forms/{fid}/edit','FormController@edit');
Route::post('/projects/{pid}/forms/{fid}/preset', 'FormController@preset');
Route::post('/projects/{pid}','FormController@store');
Route::post('/projects/{pid}/forms/{fid}/pages/modify', 'PageController@modifyFormPage');

//export routes
Route::get('/projects/{pid}/forms/{fid}/exportRecords/{type}','ExportController@exportRecords');
Route::get('/projects/{pid}/forms/{fid}/exportFiles','ExportController@exportRecordFiles');
Route::get('/projects/{pid}/forms/{fid}/exportForm','ExportController@exportForm');
Route::get('/projects/{pid}/exportProj','ExportController@exportProject');
Route::get('/checkRecordExport/{fid}', 'ExportController@checkRecordExport');

//field routes
Route::get('/projects/{pid}/forms/{fid}/fields','FormController@show'); //alias for form/{id}
Route::patch('/projects/{pid}/forms/{fid}/fields/{flid}','FieldController@update');
Route::get('/projects/{pid}/forms/{fid}/fields/create/{rootPage}','FieldController@create');
Route::get('/projects/{pid}/forms/{fid}/fields/{flid}','FieldController@show');
Route::delete('/projects/{pid}/forms/{fid}/fields/{flid}','FieldController@destroy');
Route::get('/projects/{pid}/forms/{fid}/fields/{flid}/edit','FieldController@edit');
Route::get('/projects/{pid}/forms/{fid}/fields/{flid}/options','FieldController@show'); //alias for fields/{id}
Route::get('/projects/{pid}/forms/{fid}/advOpt','FieldAjaxController@getAdvancedOptionsPage');
Route::patch('/projects/{pid}/forms/{fid}/fields/{flid}/options','FieldAjaxController@updateOptions');
Route::post('/projects/{pid}/forms/{fid}/fields/{flid}/options/validateCombo','FieldAjaxController@validateComboListOpt');
Route::post('/projects/{pid}/forms/{fid}/fields/{flid}/options/geoConvert','FieldAjaxController@geoConvert');
Route::post('/projects/{pid}/forms/{fid}/fields/{flid}/options/assoc','AssociatorSearchController@assocSearch');
Route::post('/projects/{pid}/forms/{fid}/fields/{flid}/move', 'PageController@moveField');
Route::post('/projects/{pid}/forms/{fid}','FieldController@store');
Route::post('/saveTmpFile/{flid}', 'FieldAjaxController@saveTmpFile');
Route::patch('/saveTmpFile/{flid}', 'FieldAjaxController@saveTmpFile');
Route::delete('/deleteTmpFile/{flid}/{filename}', 'FieldAjaxController@delTmpFile');
Route::get('/download/{rid}/{flid}/{filename}','FieldAjaxController@getFileDownload');
Route::get('/download/{rid}/{flid}/{filename}/{type}','FieldAjaxController@getImgDisplay');
Route::get("/validateAddress", "FieldAjaxController@validateAddress");

//record preset routes
Route::get('/projects/{pid}/forms/{fid}/records/presets', 'RecordPresetController@index');
Route::patch('/changePresetName', 'RecordPresetController@changePresetName');
Route::delete('/deletePreset', 'RecordPresetController@deletePreset');
Route::post('/getRecordArray', 'RecordPresetController@getRecordArray');
Route::post('/presetRecord', 'RecordPresetController@presetRecord');
Route::post('/getData', 'RecordPresetController@getData');
Route::post('/moveFilesToTemp', 'RecordPresetController@moveFilesToTemp');

//option preset routes
Route::get('/projects/{pid}/presets', 'OptionPresetController@index');
Route::get('/projects/{pid}/presets/create','OptionPresetController@newPreset');
Route::post('/projects/{pid}/presets/create','OptionPresetController@create');
Route::delete('/projects/{pid}/presets/delete','OptionPresetController@delete');
Route::get('/projects/{pid}/presets/{id}/edit','OptionPresetController@edit');
Route::post('/projects/{pid}/presets/{id}/saveList','OptionPresetController@saveList');
Route::post('/projects/{pid}/presets/{id}/edit','OptionPresetController@update');
Route::post('/projects/{pid}/forms/{fid}/fields/{flid}/applyPreset','OptionPresetController@applyPreset');

//record routes
Route::get('/projects/{pid}/forms/{fid}/records','RecordController@index');
Route::post('/projects/{pid}/forms/{fid}/records/createTest','RecordController@createTest');
Route::get('projects/{pid}/forms/{fid}/records/massAssignRecords','RecordController@showMassAssignmentView');
Route::post('projects/{pid}/forms/{fid}/records/massAssignRecords','RecordController@massAssignRecords');
Route::patch('/projects/{pid}/forms/{fid}/records/{rid}','RecordController@update');
Route::get('/projects/{pid}/forms/{fid}/records/create','RecordController@create');
Route::get('/projects/{pid}/forms/{fid}/records/import','RecordController@importRecordsView');
Route::post('/projects/{pid}/forms/{fid}/records/matchup','ImportController@matchupFields');
Route::post('/projects/{pid}/forms/{fid}/records/importRecord','ImportController@importRecord');
Route::get('/projects/{pid}/forms/{fid}/importExample/{type}','ImportController@exportSample');
Route::get('/projects/{pid}/forms/{fid}/records/{rid}','RecordController@show');
Route::delete('/projects/{pid}/forms/{fid}/records/{rid}','RecordController@destroy');
Route::get('/projects/{pid}/forms/{fid}/records/{rid}/edit','RecordController@edit');
Route::post('/projects/{pid}/forms/{fid}/records','RecordController@store');
Route::delete('projects/{pid}/forms/{fid}/deleteTestRecords','RecordController@deleteTestRecords');
Route::delete('projects/{pid}/forms/{fid}/deleteAllRecords','RecordController@deleteAllRecords');
Route::post('/projects/{pid}/forms/{fid}/cleanUp', 'RecordController@cleanUp');
Route::get('/projects/{pid}/forms/{fid}/clone/{rid}', 'RecordController@cloneRecord');

//revision routes
Route::get('/projects/{pid}/forms/{fid}/records/revisions/recent', 'RevisionController@index');
Route::get('/projects/{pid}/forms/{fid}/records/revisions/{rid}', 'RevisionController@show');
Route::get('/rollback', 'RevisionController@rollback');

//user routes
Route::get('/user', 'Auth\UserController@index');
Route::get('/user/profile', 'Auth\UserController@index');
Route::patch('/user/changepw', 'Auth\UserController@changepw');
Route::get('/auth/activate', 'Auth\UserController@activateshow');
Route::get('/user/activate/{token}', 'Auth\UserController@activate');
Route::post('/auth/activator', 'Auth\UserController@activator');
Route::post('/user/profile','Auth\UserController@changeprofile');
Route::post('/user/picture','Auth\UserController@changepicture');

//metadata routes
Route::get('/projects/{pid}/forms/{fid}/metadata/setup','MetadataController@index');
Route::post('/projects/{pid}/forms/{fid}/metadata/setup','MetadataController@store');
Route::post('/projects/{pid}/forms/{fid}/metadata/setup/resource','MetadataController@updateResource');
Route::post('/projects/{pid}/forms/{fid}/metadata/setup/primary','MetadataController@makePrimary');
Route::delete('/projects/{pid}/forms/{fid}/metadata/setup','MetadataController@destroy');
Route::get('/projects/{pid}/forms/{fid}/metadata/public','MetadataController@records2');
Route::get('/projects/{pid}/forms/{fid}/metadata/public/{resource}','MetadataController@singleRecord');
Route::post('/projects/{pid}/forms/{fid}/metadata/massassign','MetadataController@massAssign');

//install routes
Route::get('/install','InstallController@index');
Route::post('/install/migrate',"InstallController@runMigrate");
Route::post('/install/environment',"InstallController@installKora");
Route::get('/install/config',"InstallController@editEnvConfigs");
Route::post('/install/config',"InstallController@updateEnvConfigs");

//update routes
Route::get('/update', 'UpdateController@index');
Route::get('/update/runScripts', 'UpdateController@runScripts');

//backup routes
Route::get('/backup','BackupController@index');
Route::post('/backup/start','BackupController@create');
Route::post('/backup/finish','BackupController@finishBackup');
Route::get('/backup/download/{path}','BackupController@download');
Route::post('/backup/restore/start','BackupController@restoreData');
Route::post('/backup','BackupController@startBackup');
Route::post('/backup/restore','BackupController@startRestore');
Route::post('/backup/restore/finish','BackupController@finishRestore');
Route::post('/backup/user/unlock','BackupController@unlockUsers');
Route::post('/backup/delete','BackupController@delete');
Route::get('/backup/progress/{backup_id}','BackupController@checkProgress');
Route::get('/backup/restore/progress/{backup_id}','BackupController@checkRestoreProgress');

//form search routes
Route::get('/keywordSearch/project/{pid}/forms/{fid}', 'FormSearchController@keywordSearch');
Route::get('/keywordSearch/project/{pid}/forms/{fid}/delete', 'FormSearchController@deleteSubset');

//project search routes
Route::get("keywordSearch", 'ProjectSearchController@keywordSearch');
Route::get("keywordSearch/project/{pid}", "ProjectSearchController@keywordSearch");

//global search routes
Route::get("globalSearch", "ProjectSearchController@globalSearch");
Route::post("globalQuickSearch", "ProjectSearchController@globalQuickSearch");
Route::post("cacheGlobalSearch", "ProjectSearchController@cacheGlobalSearch");
Route::delete("clearGlobalCache", "ProjectSearchController@clearGlobalCache");

//advanced search routes
Route::get("/projects/{pid}/forms/{fid}/advancedSearch", "AdvancedSearchController@index");
Route::get("/projects/{pid}/forms/{fid}/advancedSearch/results", "AdvancedSearchController@results");
Route::post("/projects/{pid}/forms/{fid}/advancedSearch/search", "AdvancedSearchController@search");

// help routes
Route::get("/help/search", "HelpController@search");

//twitter routes
Route::get("/twitter", "TwitterController@index");

Route::controllers([
	'auth' => 'Auth\AuthController',
	'password' => 'Auth\PasswordController',
]);
Route::post("/user/projectCustom", "Auth\UserController@saveProjectCustomOrder");
Route::post("/user/formCustom/{pid}", "Auth\UserController@saveFormCustomOrder");
