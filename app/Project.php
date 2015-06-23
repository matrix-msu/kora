<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Project extends Model {

	protected $fillable = [
        'name',
        'slug',
        'description',
        'adminId',
        'active'
    ];

    protected $primaryKey = "pid";

    public function forms(){
        return $this->hasMany('App\Form','pid');
    }

    /**
     * Get the tokens associated with a given project.
     *
     * @return Token(s)
     */
    public function tokens(){
        return $this->belongsToMany('App\Token');
    }
}
