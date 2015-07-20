<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class MultiSelectListField extends Model {

    protected $fillable = [
        'rid',
        'flid',
        'options'
    ];

    protected $primaryKey = "id";

    public function record(){
        return $this->belongsTo('App\Record');
    }

}
