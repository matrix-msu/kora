<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;

class FormGroup extends Model {

    /*
    |--------------------------------------------------------------------------
    | Form Group
    |--------------------------------------------------------------------------
    |
    | This model represents the data for a Form Group
    |
    */

    /**
     * @var array - Attributes that can be mass assigned to model
     */
	protected $fillable = ['name', 'create', 'edit', 'delete'];

    /**
     * Returns the users belonging to a form group.
     *
     * @return BelongsToMany
     */
    public function users() {
        return $this->belongsToMany('App\User');
    }

    /**
     * Returns a form group's form.
     *
     * @return BelongsTo
     */
    public function form() {
        return $this->belongsTo('App\Form');
    }

    /**
     * Returns a form group's project.
     *
     * @return Project
     */
    public function project() {
        $form = $this->form()->first();
        return $form->project();
    }

    /**
     * Determines if a user is in a form group.
     *
     * @param User $user - User to verify
     * @return bool - Is member
     */
    public function hasUser(User $user) {
        $thisUsers = $this->users()->get();
        return $thisUsers->contains($user);
    }

    /**
     * Delete's the connections between group and users, and then deletes self.
     */
    public function delete() {
        DB::table("form_group_user")->where("form_group_id", "=", $this->id)->delete();

        parent::delete();
    }
}
