<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Association extends Model {

    /*
    |--------------------------------------------------------------------------
    | Association
    |--------------------------------------------------------------------------
    |
    | This model represents the association permissions of forms to other forms
    |
    */

    /**
     * @var array - Attributes that can be mass assigned to model
     */
    protected $fillable = ['data_form', 'assoc_form'];

}
