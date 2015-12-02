<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Script extends Model {

    protected $fillable = ['id', 'filename', 'hasRun'];

}
