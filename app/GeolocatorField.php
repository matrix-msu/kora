<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class GeolocatorField extends Model {

    protected $fillable = [
        'rid',
        'flid',
        'locations'
    ];

    protected $primaryKey = "id";

    public function record(){
        return $this->belongsTo('App\Record');
    }

}
