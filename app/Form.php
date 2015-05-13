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
}
