<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;

class Token extends Model {

    /*
    |--------------------------------------------------------------------------
    | Token
    |--------------------------------------------------------------------------
    |
    | This model represents an authentication token for interacting with projects
    |  from outside Kora3
    |
    */

    /**
     * @var array - Attributes that can be mass assigned to model
     */
	protected $fillable = [
        'token',
        'title',
        'search',
        'create',
        'edit',
        'delete',
    ];

    /**
     * Get the projects associated to with a token.
     *
     * @return BelongsToMany
     */
    public function projects() {
        return $this->belongsToMany('App\Project');
    }

    /**
     * Determines if a token belongs to a certain project.
     *
     * @param  Project $project - Project to check against
     * @return bool - Does belong
     */
    public function hasProject(Project $project) {
        $thisProjects = $this->projects()->get();
        return $thisProjects->contains($project);
    }
}
