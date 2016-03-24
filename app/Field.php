<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Field extends Model {

    protected $fillable = [
        'pid',
        'fid',
        'type',
        'name',
        'slug',
        'desc',
        'required',
        'searchable',
    ];

    protected $primaryKey = "flid";

    public function form(){
        return $this->belongsTo('App\Form');
    }

    public function metadata(){
        return $this->hasOne('App\Metadata','flid');
    }
}

