<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class ListField extends Model {

    protected $fillable = [
        'rid',
        'flid',
        'option'
    ];

    protected $primaryKey = "id";

    public function record(){
        return $this->belongsTo('App\Record');
    }

}
