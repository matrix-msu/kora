<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Form extends Model {

    protected $fillable = [
        'nextField',
        'name',
        'slug',
        'description'
    ];

    protected $primaryKey = ["pid","fid"];

    public function project(){
        return $this->belongsTo('App\Project');
    }
}
