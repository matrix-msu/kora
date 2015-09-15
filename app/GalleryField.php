<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class GalleryField extends Model {

    protected $fillable = [
        'rid',
        'flid',
        'images'
    ];

    protected $primaryKey = "id";

    public function record(){
        return $this->belongsTo('App\Record');
    }

}
