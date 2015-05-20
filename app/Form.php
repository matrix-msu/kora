<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Form extends Model {

    protected $fillable = [
        'pid',
        'name',
        'slug',
        'description'
    ];

    protected $primaryKey = "fid";

    public function project(){
        return $this->belongsTo('App\Project');
    }

    public function fields(){
        return $this->hasMany('App\Field', 'fid');
    }

    public function records(){
        return $this->hasMany('App\Record', 'rid');
    }
}
