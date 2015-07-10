<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class ProjectGroup extends Model {

	protected $fillable = ['name', 'create', 'edit', 'delete'];

    /**
     * Returns projects associated with a Project Group
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users(){
        return $this->belongsToMany('App\User');
    }

    /**
     * Returns a group's project.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function project(){
        return $this->belongsTo('App\Project');
    }
}
