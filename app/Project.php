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

    /**
     * Returns the forms associated with a project.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
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

    /**
     * Returns the group that has control over the project.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function group(){
        return $this->belongsTo('App\Group');
    }
}
