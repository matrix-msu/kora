<?php //TODO::CASTLE

/**
* Hyphenates a string
*
* @return string - hyphenated
*/
function str_hyphenated($string) {
    return strtolower(preg_replace("/[^\w]+/", "-", $string));
}

/**
 * Gets the available set of languages in the installation.
 *
 * @return array - the languages
 */
 function getLangs() {
     return \Illuminate\Support\Facades\Config::get('app.locales_supported');
 }

/**
 * Checks to see if kora is installed.
 *
 * @return bool - is installed
 */
 function isInstalled() {
     return file_exists("../.env");
 }

/**
 * Returns array of links
 *
 * @return array - the links
 */
function getDashboardBlockLink($block, $link_type) {
    switch ($block->type) {
        case 'Project':
            return getDashboardProjectBlockLink($block, $link_type);
            break;
        case 'Form':
            return getDashboardFormBlockLink($block, $link_type);
            break;
        default:
          return [];
    }
}

/**
 * Returns array of links
 *
 * @return array - the links
 */
function getDashboardProjectBlockLink($block, $link_type) {
  $options = json_decode($block->options, true);
  switch ($link_type) {
      case 'edit':
          return [
            'tooltip' => 'Edit Project',
            'icon-class' => 'icon-edit-little',
            'href' => action('ProjectController@edit', ['pid'=>$options['pid']]),
			'type' => 'edit'
          ];
          break;
      case 'search':
          return [
            'tooltip' => 'Search Project Records',
            'icon-class' => 'icon-search',
            'href' => action('ProjectSearchController@keywordSearch', ['pid'=>$options['pid']]),
			'type' => 'search'
          ];
          break;
      case 'form-import':
          return [
            'tooltip' => 'Import Form',
            'icon-class' => 'icon-form-import-little',
            'href' => action('FormController@importFormView', ['pid'=>$options['pid']]),
			'type' => 'form-import'
          ];
          break;
      case 'form-new':
          return [
            'tooltip' => 'Create New Form',
            'icon-class' => 'icon-form-new-little',
            'href' => action('FormController@create', ['pid'=>$options['pid']]),
			'type' => 'form-new'
          ];
          break;
      case 'permissions':
          return [
            'tooltip' => 'Project Permissions',
            'icon-class' => 'icon-star',
            'href' => action('ProjectGroupController@index', ['pid'=>$options['pid']]),
			'type' => 'permissions'
          ];
          break;
      case 'presets':
          return [
            'tooltip' => 'Field Value Presets',
            'icon-class' => 'icon-preset-Little',
            'href' => action('OptionPresetController@index', ['pid'=>$options['pid']]),
			'type' => 'presets'
          ];
          break;
	  case 'import':
	      return [
            'tooltip' => 'Import Multi-Form Records Setup',
            'icon-class' => 'icon-importMFRecords-little',
		    'href' => url('/').'/projects/'.$options['pid'].'/importMF',
		    'type' => 'import'
		  ];
		  break;
	  case 'import2k':
          return [
            'tooltip' => 'Kora 2 Scheme Importer',
            'icon-class' => 'icon-k2SchemeImporter-little',
            'href' => url('/').'/projects/'.$options['pid'].'/forms/importk2',
            'type' => 'import2k'
          ];
          break;
	  case 'export':
	      return [
            'tooltip' => 'Export Project',
            'icon-class' => 'icon-exportProject-little',
		    'href' => action('ExportController@exportProject',['pid' => $options['pid']]),
		    'type' => 'export'
		  ];
		  break;
      default:
        return [];
  }
}

/**
 * Returns array of links
 *
 * @return array - the links
 */
function getDashboardFormBlockLink($block, $link_type) {
  $options = json_decode($block->options, true);
  $form = \App\Http\Controllers\FormController::getForm($options['fid']);
  switch ($link_type) {
      case 'edit':
          return [
            'tooltip' => 'Edit Form',
            'icon-class' => 'icon-edit-little',
            'href' => action('FormController@edit', ['pid' => $form->pid, 'fid' => $form->fid]),
			'type' => 'edit'
          ];
          break;
      case 'search':
          return [
            'tooltip' => 'Search Form Records',
            'icon-class' => 'icon-search',
            'href' => action('RecordController@index', ['pid' => $form->pid, 'fid' => $form->fid]),
			'type' => 'search'
          ];
          break;
      case 'record-new':
          return [
            'tooltip' => 'Create New Record',
            'icon-class' => 'icon-record-new-little',
            'href' => action('RecordController@create',['pid' => $form->pid, 'fid' => $form->fid]),
			'type' => 'record-new'
          ];
          break;
      case 'field-new':
          $lastPage = \App\Page::where('fid','=',$form->fid)->orderBy('sequence','desc')->first();
          return [
            'tooltip' => 'Create New Field',
            'icon-class' => 'icon-form-new-little',
            'href' => action('FieldController@create', ['pid'=>$form->pid, 'fid' => $form->fid, 'rootPage' => $lastPage['id']]),
			'type' => 'field-new'
          ];
          break;
      case 'form-permissions':
          return [
            'tooltip' => 'Form Permissions',
            'icon-class' => 'icon-star',
            'href' => action('FormGroupController@index', ['pid' => $form->pid, 'fid' => $form->fid]),
			'type' => 'form-permissions'
          ];
          break;
      case 'revisions':
          return [
            'tooltip' => 'Manage Record Revisions',
            'icon-class' => 'icon-preset-Little',
            'href' => action('RevisionController@index', ['pid'=>$form->pid, 'fid'=>$form->fid]),
			'type' => 'revisions'
          ];
          break;
	  case 'import':
	      return [
            'tooltip' => 'Import Records',
            'icon-class' => 'icon-importrecords-little',
		    'href' => action('RecordController@importRecordsView', ['pid' => $form->pid, 'fid' => $form->fid]),
		    'type' => 'import'
		  ];
          break;
      case 'batch':
          return [
            'tooltip' => 'Batch Assign Field Values',
            'icon-class' => 'icon-batchAssign-little',
            'href' => action('RecordController@showMassAssignmentView',['pid' => $form->pid, 'fid' => $form->fid]),
            'type' => 'batch'
          ];
          break;
      case 'export-records':
          return [
            'tooltip' => 'Export All Records',
            'icon-class' => 'icon-exportRecords-Little',
            'href' => '#',
            'type' => 'export-records'
          ];
          break;
      case 'assoc-permissions':
          return [
            'tooltip' => 'Association Permissions',
            'icon-class' => 'icon-associationPermissions-little',
            'href' => action('AssociationController@index', ['fid' => $form->fid, 'pid' => $form->pid]),
            'type' => 'assoc-permissions'
          ];
          break;
      case 'export-form':
          return [
            'tooltip' => 'Export Form',
            'icon-class' => 'icon-exportForm-Little',
            'href' => action('ExportController@exportForm',['fid'=>$form->fid, 'pid' => $form->pid]),
            'type' => 'export-form'
          ];
          break;
      default:
        return [];
  }
}

/**
 * Returns array of links
 *
 * @return array - the links
 */
function getDashboardRecordBlockLink($record) {
    return array(
        [
            'tooltip' => 'Edit Record',
            'icon-class' => 'icon-edit-little',
            'href' => action('RecordController@edit', ['pid' => $record->pid, 'fid' => $record->fid, 'rid' => $record->rid])
        ],
        [
            'tooltip' => 'Duplicate Record',
            'icon-class' => 'icon-duplicate-little',
            'href' => action('RecordController@cloneRecord', ['pid' => $record->pid, 'fid' => $record->fid, 'rid' => $record->rid])
        ],
        [
            'tooltip' => 'View Revisions',
            'icon-class' => 'icon-clock-little',
            'href' => action('RevisionController@show', ['pid' => $record->pid, 'fid' => $record->fid, 'rid' => $record->rid])
        ]
    );
}
