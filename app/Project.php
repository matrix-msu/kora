<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Project extends Model {

	protected $fillable = [
        'name',
        'slug',
        'description',
        'adminGID',
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
     * Returns the project's admin group.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function adminGroup(){
        return $this->belongsTo('App\ProjectGroup', 'adminGID');
    }

    /**
     * Returns the groups associated with a project.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function groups(){
        return $this->hasMany('App\ProjectGroup','pid');
    }

    public function optionPresets(){
        return $this->hasMany('App\OptionPreset','pid');
    }

}
