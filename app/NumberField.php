<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class NumberField extends Model {

    protected $fillable = [
        'rid',
        'flid',
        'number'
    ];

    protected $primaryKey = "id";

    public function record(){
        return $this->belongsTo('App\Record');
    }

}
