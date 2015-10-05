<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class VideoField extends Model {

    protected $fillable = [
        'rid',
        'flid',
        'video'
    ];

    protected $primaryKey = "id";

    public function record(){
        return $this->belongsTo('App\Record');
    }

}
