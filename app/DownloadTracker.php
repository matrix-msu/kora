<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class DownloadTracker extends Model {

    /*
    |--------------------------------------------------------------------------
    | Download Tracker
    |--------------------------------------------------------------------------
    |
    | This model represents the tracker that monitors progress of record exporting
    |
    */

    /**
     * @var array - Attributes that can be mass assigned to model
     */
    protected $fillable = ["fid"];
}
