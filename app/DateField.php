<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class DateField extends Model {

    protected $fillable = [
        'rid',
        'flid',
        'month',
        'day',
        'year',
        'era'
    ];

    protected $primaryKey = "id";

    public function record(){
        return $this->belongsTo('App\Record');
    }

}
