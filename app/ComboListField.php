<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class ComboListField extends Model {

    protected $fillable = [
        'rid',
        'flid',
        'options',
        'ftype1',
        'ftype2'
    ];

    protected $primaryKey = "id";

    public function record(){
        return $this->belongsTo('App\Record');
    }

}
