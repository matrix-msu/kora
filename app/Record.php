<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Record extends Model {

    protected $fillable = [
        'id',
        'pid',
        'fid',
        'owner',
        'kid'
    ];

    protected $primaryKey = "rid";

    public function preset() {
        return $this->belongsTo('App/Preset');
    }

    public function form(){
        return $this->belongsTo('App\Form', 'fid');
    }

    public function textfields(){
        return $this->hasMany('App\TextField', 'rid');
    }

    public function richtextfields(){
        return $this->hasMany('App\RichTextField', 'rid');
    }

    public function numberfields(){
        return $this->hasMany('App\NumberField', 'rid');
    }

    public function listfields(){
        return $this->hasMany('App\ListField', 'rid');
    }

    public function multiselectlistfields(){
        return $this->hasMany('App\MultiSelectListField', 'rid');
    }

    public function generatedlistfields(){
        return $this->hasMany('App\GeneratedListField', 'rid');
    }

    public function combolistfields(){
        return $this->hasMany('App\ComboListField', 'rid');
    }

    public function datefields(){
        return $this->hasMany('App\DateField', 'rid');
    }

    public function schedulefields(){
        return $this->hasMany('App\ScheduleField', 'rid');
    }

    public function geolocatorfields(){
        return $this->hasMany('App\GeolocatorField', 'rid');
    }

    public function documentsfields(){
        return $this->hasMany('App\DocumentsField', 'rid');
    }

    public function galleryfields(){
        return $this->hasMany('App\GalleryField', 'rid');
    }

    public function playlistfields(){
        return $this->hasMany('App\PlaylistField', 'rid');
    }

    public function videofields(){
        return $this->hasMany('App\VideoField', 'rid');
    }

    public function modelfields(){
        return $this->hasMany('App\ModelField', 'rid');
    }

    public function associatorfields(){
        return $this->hasMany('App\AssociatorField', 'rid');
    }

    public function owner(){
        return $this->hasOne('App\User', 'owner');
    }

}

