<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Timer extends Model {

    /*
    |--------------------------------------------------------------------------
    | Timer
    |--------------------------------------------------------------------------
    |
    | This model represents the global timers in kora
    |
    */

    /**
     * @var array - This is an array of all the global timers, primarily for install purposes
     */
    static public $globalTimers = [
        "reverse_assoc_cache_build",
        "last_record_updated"
    ];

    /**
     * @var array - Attributes that can be mass assigned to model
     */
    protected $fillable = ['name', 'interval'];

}
