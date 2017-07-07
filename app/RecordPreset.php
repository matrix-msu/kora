<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class RecordPreset extends Model {

    /*
    |--------------------------------------------------------------------------
    | Record Preset
    |--------------------------------------------------------------------------
    |
    | This model represents a record that has been made a preset
    |
    */

    /**
     * @var array - Attributes that can be mass assigned to model
     */
    protected $fillable = ['name', 'fid', 'rid'];

}
