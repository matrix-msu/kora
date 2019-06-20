<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Version extends Model {

    /*
    |--------------------------------------------------------------------------
    | Version
    |--------------------------------------------------------------------------
    |
    | This model represents the current version of kora
    |
    */

    /**
     * @var array - Attributes that can be mass assigned to model
     */
    protected $fillable = ['id', 'version'];

}
