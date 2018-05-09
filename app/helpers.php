<?php

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
 * Gets the default meme list for special file fields.
 *
 * @return bool - is installed
 */
function getDefaultTypes($type) {
    return \App\FileTypeField::$FILE_MIME_TYPES[$type];
}

/**
 * Returns a thing
 *
 * @return bool - is installed
 */
function getDashboardBlockLink($block, $link_type) {
    $options = json_decode($block->options, true);
    switch ($link_type) {
        case 'edit':
            return [
              'tooltip' => 'Edit Project',
              'icon-class' => 'icon-edit-little',
              'href' => action('ProjectController@edit', ['pid'=>$options['pid']])
            ];
            break;
        case 'search':
            return [
              'tooltip' => 'Search Project Records',
              'icon-class' => 'icon-search',
              'href' => action('ProjectSearchController@keywordSearch', ['pid'=>$options['pid']])
            ];
            break;
        case 'form-import':
            return [
              'tooltip' => 'Import Form',
              'icon-class' => 'icon-form-import-little',
              'href' => action('FormController@importFormView', ['pid'=>$options['pid']])
            ];
            break;
        case 'form-new':
            return [
              'tooltip' => 'Create New Form',
              'icon-class' => 'icon-form-new-little',
              'href' => action('FormController@create', ['pid'=>$options['pid']])
            ];
            break;
        case 'permissions':
            return [
              'tooltip' => 'Project Permissions',
              'icon-class' => 'icon-star',
              'href' => action('ProjectGroupController@index', ['pid'=>$options['pid']])
            ];
            break;
        case 'presets':
            return [
              'tooltip' => 'Field Value Presets',
              'icon-class' => 'icon-preset-little',
              'href' => action('OptionPresetController@index', ['pid'=>$options['pid']])
            ];
            break;
        default:
          return [
            'tooltip' => 'Search Project Records',
            'icon-class' => 'icon-search',
            'href' => action('ProjectSearchController@keywordSearch', ['pid'=>$options['pid']])
          ];
    }
}
