<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class TextField extends Model {

    protected $fillable = [
        'rid',
        'flid',
        'text'
    ];

    protected $primaryKey = "id";

    public function record(){
        return $this->belongsTo('App\Record');
    }
}
