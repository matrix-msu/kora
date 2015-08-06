<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Record extends Model {

    protected $fillable = [
        'pid',
        'fid',
        'owner',
        'kid'
    ];

    protected $primaryKey = "rid";

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

    public function datefields(){
        return $this->hasMany('App\DateField', 'rid');
    }

    public function schedulefields(){
        return $this->hasMany('App\ScheduleField', 'rid');
    }

    public function owner(){
        return $this->hasOne('App\User', 'owner');
    }

}

