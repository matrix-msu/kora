<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Field extends Model {

    protected $fillable = [
        'pid',
        'fid',
        'order',
        'type',
        'name',
        'slug',
        'desc',
        'required',
        //'default',
        //'options'
    ];

    protected $primaryKey = ["flid", 'fid', 'pid'];

    public function form(){
        return $this->belongsTo('App\Form');
    }
}

