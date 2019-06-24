<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Script extends Model {

    /*
    |--------------------------------------------------------------------------
    | Script
    |--------------------------------------------------------------------------
    |
    | This model represents an update script for kora
    |
    */

    /**
     * @var array - Attributes that can be mass assigned to model
     */
    protected $fillable = ['id', 'filename', 'has_run'];

}
