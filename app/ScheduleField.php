<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class ScheduleField extends Model {

    protected $fillable = [
        'rid',
        'flid',
        'events'
    ];

    protected $primaryKey = "id";

    public function record(){
        return $this->belongsTo('App\Record');
    }

}
