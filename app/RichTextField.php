<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class RichTextField extends Model {

    protected $fillable = [
        'rid',
        'flid',
        'rawtext'
    ];

    protected $primaryKey = "id";

    public function record(){
        return $this->belongsTo('App\Record');
    }

}
