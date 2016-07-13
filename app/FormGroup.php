<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class FormGroup extends Model {

	protected $fillable = ['name', 'create', 'edit', 'delete'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users(){
        return $this->belongsToMany('App\User');
    }

    /**
     * Returns a form group's form.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function form(){
        return $this->belongsTo('App\Form');
    }

    /**
     * Returns a form group's project.
     *
     * @return mixed
     */
    public function project(){
        $form = $this->form()->first();
        return $form->project();
    }

    /**
     * Determines if a user is in a form group.
     *
     * @param User $user
     * @return bool
     */
    public function hasUser(User $user){
        $thisUsers = $this->users()->get();
        return $thisUsers->contains($user);
    }

    public function delete() {
        DB::table("form_group_user")->where("form_group_id", "=", $this->id)->delete();

        parent::delete();
    }
}
