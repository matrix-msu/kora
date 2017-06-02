<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Metadata extends Model {

    //
    protected $fillable = [
        'pid',
        'fid',
        'flid',
        'name',
        'primary'
    ];

    protected $primaryKey = "flid";


    public function field(){
        return $this->belongsTo('App\Field','flid','flid');
    }
}
