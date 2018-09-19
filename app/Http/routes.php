<?php

Route::group(['middleware' => 'web'], function () {
    Route::get('/', 'WelcomeController@index');
    Route::get('/home', 'WelcomeController@index');
    Route::post('/language', 'WelcomeController@setTemporaryLanguage');
    Route::get('/dashboard', 'DashboardController@dashboard');
    Route::get('/email', 'HelpController@emailTest'); //TEST ROUTE

//project routes
    Route::get('/projects/import', 'ProjectController@importProjectView');
	Route::post('/projects/getProjectPermissionsModal', 'ProjectController@getProjectPermissionsModal');
    Route::post('/projects/import', 'ImportController@importProject');
    Route::resource('projects', 'ProjectController');
    Route::post('projects/request', 'ProjectController@request');
    Route::post('projects/{pid}/archive', 'ProjectController@setArchiveProject');
    Route::get('/projects/{pid}/importMF', 'ImportMultiFormController@index');
    Route::post('/projects/{pid}/importMF', 'ImportMultiFormController@beginImport');
    Route::post('/projects/{pid}/importMFRecord', 'ImportMultiFormController@importRecord');
    Route::post('/projects/{pid}/importMFAssoc', 'ImportMultiFormController@crossFormAssociations');
    Route::post('/saveTmpFileMF', 'ImportMultiFormController@saveTmpFile');
    Route::delete('/deleteTmpFileMF/{filename}', 'ImportMultiFormController@delTmpFile');
    Route::post('projects/validate', 'ProjectController@validateProjectFields');
    Route::patch('projects/validate/{projects}', 'ProjectController@validateProjectFields');

//project group routes
    Route::get('/projects/{pid}/manage/projectgroups/', 'ProjectGroupController@index');
    Route::post('/projects/{pid}/manage/projectgroups/create', 'ProjectGroupController@create');
    Route::patch('projects/{pid}/manage/projectgroups/removeUser', 'ProjectGroupController@removeUser');
    Route::patch('projects/{pid}/manage/projectgroups/addUsers', 'ProjectGroupController@addUsers');
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
    Route::get('/admin/users/{id}/edit', 'AdminController@editUser');
    Route::patch('/admin/update/{id}', 'AdminController@update');
    Route::patch('/admin/updateActivation/{id}', 'AdminController@updateActivation');
    Route::patch('/admin/updateStatus/{id}', 'AdminController@updateStatus');
    Route::patch('/admin/batch', 'AdminController@batch');
    Route::delete('admin/deleteUser/{id}', 'AdminController@deleteUser');
    Route::post('/admin/order66', 'AdminController@deleteData');

//Kora Exodus routes
    Route::get('/exodus', 'ExodusController@index');
    Route::post('/exodus/projects', 'ExodusController@getProjectList');
    Route::post('/exodus/migrate', 'ExodusController@migrate');
    Route::get('/exodus/progress', 'ExodusController@checkProgress');
    Route::post('/exodus/user/unlock', 'ExodusController@unlockUsers');
    Route::post('/exodus/start', 'ExodusController@startExodus');
    Route::post('/exodus/finish', 'ExodusController@finishExodus'); //

//Kora Publisher
    Route::get('/publish', 'PublishController@index');

//token routes
    Route::get('/tokens', 'TokenController@index');
    Route::post('/tokens/create', 'TokenController@create');
    Route::post('/tokens/store', 'TokenController@edit');
    Route::post('/tokens/unassigned', 'TokenController@getUnassignedProjects');
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
    Route::delete('/projects/{pid}/forms/{fid}/assocReverse', 'AssociationController@destroyReverse');

//form routes
    Route::get('/projects/{pid}/forms', 'ProjectController@show'); //alias for project/{id}
    Route::post('projects/{pid}/forms/validate', 'FormController@validateFormFields');
    Route::patch('projects/{pid}/forms/validate/{fid}', 'FormController@validateFormFields');
    Route::patch('/projects/{pid}/forms/{fid}', 'FormController@update');
    Route::get('/projects/{pid}/forms/create', 'FormController@create');
    Route::get('/projects/{pid}/forms/import', 'FormController@importFormView');
    Route::get('/projects/{pid}/forms/importk2', 'FormController@importFormViewK2');
    Route::post('/projects/{pid}/forms/import', 'ImportController@importForm');
    Route::post('/projects/{pid}/forms/importk2', 'ImportController@importFormK2');
    Route::get('/projects/{pid}/forms/{fid}', 'FormController@show');
    Route::delete('/projects/{pid}/forms/{fid}', 'FormController@destroy');
    Route::get('/projects/{pid}/forms/{fid}/edit', 'FormController@edit');
    Route::post('/projects/{pid}/forms/{fid}/preset', 'FormController@preset');
    Route::post('/projects/{pid}', 'FormController@store');
    Route::post('/projects/{pid}/forms/{fid}/pages/modify', 'PageController@modifyFormPage');
    Route::post('/projects/{pid}/forms/{fid}/pages/layout', 'PageController@saveFullFormLayout');

//export routes
    Route::get('/projects/{pid}/forms/{fid}/exportRecords/{type}', 'ExportController@exportRecords');
    Route::get('/projects/{pid}/forms/{fid}/exportSelectedRecords/{type}', 'ExportController@exportSelectedRecords');
    Route::post('/projects/{pid}/forms/{fid}/prepFiles', 'ExportController@prepRecordFiles');
    Route::get('/projects/{pid}/forms/{fid}/exportFiles', 'ExportController@exportRecordFiles');
    Route::get('/projects/{pid}/forms/{fid}/exportForm', 'ExportController@exportForm');
    Route::get('/projects/{pid}/exportProj', 'ExportController@exportProject');
    Route::get('/checkRecordExport/{fid}', 'ExportController@checkRecordExport');

//field routes
    Route::get('/projects/{pid}/forms/{fid}/fields', 'FormController@show'); //alias for form/{id}
    Route::post('projects/{pid}/forms/{fid}/fields/validate', 'FieldController@validateFieldFields');
    Route::patch('projects/{pid}/forms/{fid}/fields/validate/{flid}', 'FieldController@validateFieldFields');
    Route::patch('/projects/{pid}/forms/{fid}/fields/{flid}', 'FieldController@update');
    Route::get('/projects/{pid}/forms/{fid}/fields/create/{rootPage}', 'FieldController@create');
    Route::get('/projects/{pid}/forms/{fid}/fields/{flid}', 'FieldController@show');
    Route::delete('/projects/{pid}/forms/{fid}/fields/{flid}', 'FieldController@destroy');
    Route::get('/projects/{pid}/forms/{fid}/fields/{flid}/edit', 'FieldController@edit');
    Route::get('/projects/{pid}/forms/{fid}/fields/{flid}/options', 'FieldController@show'); //alias for fields/{id}
    Route::post('/projects/{pid}/forms/{fid}/advOpt', 'FieldAjaxController@getAdvancedOptionsPage');
    Route::patch('/projects/{pid}/forms/{fid}/fields/{flid}/flag', 'FieldController@updateFlag');
    Route::post('/projects/{pid}/forms/{fid}/fields/{flid}/options/validateCombo', 'FieldAjaxController@validateComboListOpt');
    Route::post('/projects/{pid}/forms/{fid}/fields/{flid}/options/geoConvert', 'FieldAjaxController@geoConvert');
    Route::post('/projects/{pid}/forms/{fid}/fields/{flid}/options/assoc', 'AssociatorSearchController@assocSearch');
    Route::post('/projects/{pid}/forms/{fid}/fields/{flid}/move', 'PageController@moveField');
    Route::post('/projects/{pid}/forms/{fid}', 'FieldController@store');
    Route::post('/saveTmpFile/{flid}', 'FieldAjaxController@saveTmpFile');
    Route::patch('/saveTmpFile/{flid}', 'FieldAjaxController@saveTmpFile');
    Route::delete('/deleteTmpFile/{flid}/{filename}', 'FieldAjaxController@delTmpFile');
    Route::get('/download/{rid}/{flid}/{filename}', 'FieldAjaxController@getFileDownload');
    Route::get('/download/{rid}/{flid}/{filename}/zip', 'FieldAjaxController@getZipDownload');
    Route::get('/download/{rid}/{flid}/{filename}/{type}', 'FieldAjaxController@getImgDisplay');
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
    Route::get('/projects/{pid}/presets/create', 'OptionPresetController@newPreset');
    Route::post('/projects/{pid}/presets/create', 'OptionPresetController@create');
    Route::post('/projects/{pid}/presets/createApi', 'OptionPresetController@createApi');
    Route::post('projects/{pid}/presets/validate', 'OptionPresetController@validatePresetFormFields');
    Route::delete('/projects/{pid}/presets/delete', 'OptionPresetController@delete');
    Route::get('/projects/{pid}/presets/{id}/edit', 'OptionPresetController@edit');
    Route::post('/projects/{pid}/presets/{id}/edit', 'OptionPresetController@update');

//record routes
    Route::get('/projects/{pid}/forms/{fid}/records', 'RecordController@index');
    Route::post('/projects/{pid}/forms/{fid}/records/createTest', 'RecordController@createTest');
    Route::get('projects/{pid}/forms/{fid}/records/massAssignRecords', 'RecordController@showMassAssignmentView');
    Route::get('projects/{pid}/forms/{fid}/records/showSelectedAssignmentView', 'RecordController@showSelectedAssignmentView');//this
    Route::post('projects/{pid}/forms/{fid}/records/massAssignRecords', 'RecordController@massAssignRecords');
    Route::post('projects/{pid}/forms/{fid}/records/massAssignRecordSet', 'RecordController@massAssignRecordSet');
    Route::patch('/projects/{pid}/forms/{fid}/records/{rid}', 'RecordController@update');
    Route::get('/projects/{pid}/forms/{fid}/records/create', 'RecordController@create');
    Route::get('/projects/{pid}/forms/{fid}/records/import', 'RecordController@importRecordsView');
    Route::post('/projects/{pid}/forms/{fid}/records/matchup', 'ImportController@matchupFields');
    Route::post('/projects/{pid}/forms/{fid}/records/validate', 'RecordController@validateRecord');
    Route::post('/projects/{pid}/forms/{fid}/records/validateMass', 'RecordController@validateMassRecord');
    Route::post('/projects/{pid}/forms/{fid}/records/importRecord', 'ImportController@importRecord');
    Route::post('/projects/{pid}/forms/{fid}/records/importRecordFailed', 'ImportController@downloadFailedRecords');
    Route::post('/projects/{pid}/forms/{fid}/records/importReasonsFailed', 'ImportController@downloadFailedReasons');
    Route::get('/projects/{pid}/forms/{fid}/importExample/{type}', 'ImportController@exportSample');
    Route::get('/projects/{pid}/forms/{fid}/records/{rid}', 'RecordController@show');
    Route::get('/projects/{pid}/forms/{fid}/records/{rid}/edit', 'RecordController@edit');
    Route::post('/projects/{pid}/forms/{fid}/records', 'RecordController@store');
    Route::delete('projects/{pid}/forms/{fid}/deleteTestRecords', 'RecordController@deleteTestRecords');
    Route::delete('projects/{pid}/forms/{fid}/records/deleteMultipleRecords', 'RecordController@deleteMultipleRecords');
    Route::delete('projects/{pid}/forms/{fid}/records/{rid}', 'RecordController@destroy');
    Route::delete('projects/{pid}/forms/{fid}/deleteAllRecords', 'RecordController@deleteAllRecords');
    Route::post('/projects/{pid}/forms/{fid}/cleanUp', 'RecordController@cleanUp');
    Route::get('/projects/{pid}/forms/{fid}/clone/{rid}', 'RecordController@cloneRecord');
    Route::get('/projects/{pid}/forms/{fid}/records/{rid}/fields/{flid}/{type}/{filename}', 'FieldController@singleResource');

//revision routes
    Route::get('/projects/{pid}/forms/{fid}/records/revisions/recent', 'RevisionController@index');
    Route::get('/projects/{pid}/forms/{fid}/records/revisions/{rid}', 'RevisionController@show');
    Route::get('/rollback', 'RevisionController@rollback');

//user routes
    Route::get('/user', 'Auth\UserController@redirect');
    Route::get('/auth/activate', 'Auth\UserController@activateshow');
    Route::get('/user/activate/{token}', 'Auth\UserController@activate');
    Route::get('/user/{uid}/edit', 'Auth\UserController@editProfile');
    Route::get('/user/{uid}/preferences', 'Auth\UserController@preferences');
    Route::get('/user/{uid}/{section?}', 'Auth\UserController@index');
    Route::delete('/user/{uid}/delete', 'Auth\UserController@delete');
    Route::patch('/user/validate/{uid}', 'Auth\UserController@validateUserFields');
    Route::patch('/user/changepw', 'Auth\UserController@changepw');
    Route::patch('/user/{uid}/update', 'Auth\UserController@update');
    Route::patch('/user/{uid}/preferences', 'Auth\UserController@updatePreferences');
    Route::post('/auth/resendActivate', 'Auth\UserController@resendActivation');
    Route::post('/auth/activator', 'Auth\UserController@activator');
    Route::post('/user/profile', 'Auth\UserController@changeprofile');
    Route::post('/user/picture', 'Auth\UserController@changepicture');
    Route::post('/user/validate', 'Auth\RegisterController@validateUserFields');

//metadata routes
    Route::get('/projects/{pid}/forms/{fid}/metadata/setup', 'MetadataController@index');
    Route::post('/projects/{pid}/forms/{fid}/metadata/setup', 'MetadataController@store');
    Route::post('/projects/{pid}/forms/{fid}/metadata/setup/resource', 'MetadataController@updateResource');
    Route::post('/projects/{pid}/forms/{fid}/metadata/setup/primary', 'MetadataController@makePrimary');
    Route::delete('/projects/{pid}/forms/{fid}/metadata/setup', 'MetadataController@destroy');
    Route::get('/projects/{pid}/forms/{fid}/metadata/public', 'MetadataController@records2');
    Route::get('/projects/{pid}/forms/{fid}/metadata/public/{resource}', 'MetadataController@singleRecord');
    Route::post('/projects/{pid}/forms/{fid}/metadata/massassign', 'MetadataController@massAssign');

//install routes
    Route::get('/helloworld', 'InstallController@helloworld');
    Route::get('/install', 'InstallController@index');
    Route::post('/install/begin', "InstallController@install");
    Route::post('/install/finish', "InstallController@installPartTwo");
    Route::get('/readyplayerone', "WelcomeController@installSuccess");
    Route::get('/install/config', "InstallController@editEnvConfigs");
    Route::post('/install/config', "InstallController@updateEnvConfigs");

//update routes
    Route::get('/update', 'UpdateController@index');
    Route::get('/update/runScripts', 'UpdateController@runScripts');

//backup routes
    Route::get('/backup', 'BackupController@index'); //
    Route::post('/backup/start', 'BackupController@create'); //
    Route::post('/backup/finish', 'BackupController@finishBackup'); //
    Route::get('/backup/download/{path}', 'BackupController@download'); //
    Route::post('/backup/restore/start', 'BackupController@restoreData');
    Route::post('/backup', 'BackupController@startBackup'); //
    Route::post('/backup/restore', 'BackupController@startRestore');
    Route::post('/backup/restore/finish', 'BackupController@finishRestore');
    Route::post('/backup/user/unlock', 'BackupController@unlockUsers');
    Route::delete('/backup/delete', 'BackupController@delete');
    Route::get('/backup/progress', 'BackupController@checkProgress');
    Route::get('/backup/restore/progress', 'BackupController@checkRestoreProgress');

//form search routes
    Route::get('/keywordSearch/project/{pid}/forms/{fid}', 'FormSearchController@keywordSearch');
    Route::get('/keywordSearch/project/{pid}/forms/{fid}/delete', 'FormSearchController@deleteSubset');

//project search routes
    Route::get("keywordSearch/project/{pid}", "ProjectSearchController@keywordSearch");

//global search routes
    Route::get("globalSearch", "ProjectSearchController@globalSearch");
    Route::post("globalQuickSearch", "ProjectSearchController@globalQuickSearch");
    Route::post("cacheGlobalSearch", "ProjectSearchController@cacheGlobalSearch");
    Route::delete("clearGlobalCache", "ProjectSearchController@clearGlobalCache");

//advanced search routes
    Route::get("/projects/{pid}/forms/{fid}/advancedSearch/results", "AdvancedSearchController@recent");
    Route::post("/projects/{pid}/forms/{fid}/advancedSearch/results", "AdvancedSearchController@search");

// help routes
    Route::get("/help/search", "HelpController@search");

//twitter routes
    Route::get("/twitter", "TwitterController@index");

//user auth
    Auth::routes();

    Route::post("/user/projectCustom", "Auth\UserController@saveProjectCustomOrder");
    Route::post("/user/formCustom/{pid}", "Auth\UserController@saveFormCustomOrder");

});

Route::group(['middleware' => 'api'], function () {
//api routes
    Route::get('/api/version', 'RestfulController@getKoraVersion');
    Route::get('/api/projects/{pid}/forms', 'RestfulController@getProjectForms');
    Route::post('/api/projects/{pid}/forms/create', 'RestfulController@createForm');
    Route::get('/api/projects/{pid}/forms/{fid}/fields', 'RestfulController@getFormFields');
    Route::get('/api/projects/{pid}/forms/{fid}/recordCount', 'RestfulController@getFormRecordCount');
    Route::post('/api/search', 'RestfulController@search');
    Route::delete('/api/delete', 'RestfulController@delete');
    Route::post('/api/create', 'RestfulController@create');
    Route::put('/api/edit', 'RestfulController@edit');
});
