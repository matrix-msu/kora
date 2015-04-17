<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Project extends Model {

	protected $fillable = [
        'nextModel',
        'name',
        'slug',
        'description',
        'adminId',
        'active'
    ];

    protected $primaryKey = "pid";

    public function forms(){
        return $this->hasMany('App\Form');
    }
}
