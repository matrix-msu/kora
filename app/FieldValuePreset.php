<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FieldValuePreset extends Model {

    /*
    |--------------------------------------------------------------------------
    | Option Preset
    |--------------------------------------------------------------------------
    |
    | This model represents an option preset for use in a field
    |
    */

    /**
     * @var array - Attributes that can be mass assigned to model
     */
    protected $fillable = ['project_id', 'preset'];

    /**
     * Returns the project this preset is owned by.
     *
     * @return BelongsTo - DESCRIPTION
     */
	public function project() {
        return $this->belongsTo('App\Project','project_id');
    }

}