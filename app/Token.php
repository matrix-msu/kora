<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Token extends Model {

	protected $fillable = [
        'token',
        'type'
    ];

    /**
     * Get the projects associated to with a token.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function projects(){
        return $this->belongsToMany('App\Project');
    }

    public function hasProject(Project $project)
    {
        $thisProjects = $this->projects()->get();
        return $thisProjects->contains($project);
    }
}
