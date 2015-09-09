<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class AssociatorField extends Model {

    protected $fillable = [
        'rid',
        'flid',
        'records'
    ];

    protected $primaryKey = "id";

    public function record(){
        return $this->belongsTo('App\Record');
    }

}
