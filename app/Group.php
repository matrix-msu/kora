<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Group extends Model {

    protected $fillable = ['name'];

    /**
     * Returns users associated with group.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany('App\User');
    }

    /**
     * Returns projects associated with a group.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function projects()
    {
        return $this->hasMany('App\Projects');
    }

    public function hasUser(User $user)
    {
        $thisUsers = $this->users()->get();
        return $thisUsers->contains($user);
    }


}
