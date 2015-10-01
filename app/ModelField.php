<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class ModelField extends Model {

    protected $fillable = [
        'rid',
        'flid',
        'model'
    ];

    protected $primaryKey = "id";

    public function record(){
        return $this->belongsTo('App\Record');
    }

}
