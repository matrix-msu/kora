<?php namespace App\KoraFields;

use Illuminate\Database\Eloquent\Model;

abstract class BaseField extends Model {

    /*
    |--------------------------------------------------------------------------
    | Base Field
    |--------------------------------------------------------------------------
    |
    | This model represents the abstract class for all typed fields in Kora3
    |
    */


    abstract function getDefaultOptions();

    abstract function addDatabaseColumn($fid, $slug, $options = null);
}