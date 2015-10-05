<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class PlaylistField extends Model {

    protected $fillable = [
        'rid',
        'flid',
        'audio'
    ];

    protected $primaryKey = "id";

    public function record(){
        return $this->belongsTo('App\Record');
    }

}
