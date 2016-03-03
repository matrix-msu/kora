<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Form extends Model {

    protected $fillable = [
        'pid',
        'name',
        'slug',
        'description',
        'public_metadata'
    ];

    protected $primaryKey = "fid";

    /**
     * Returns the project associated with a form.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function project(){
        return $this->belongsTo('App\Project', 'pid');
    }

    /**
     * Returns the fields associtated with a form
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function fields(){
        return $this->hasMany('App\Field', 'fid');
    }

    /**
     * Returns the records associated with a form.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function records(){
        return $this->hasMany('App\Record', 'fid');
    }

    /**
     * Returns the form's admin group.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function adminGroup(){
        return $this->belongsTo('App\FormGroup', 'adminGID');
    }

    /**
     * Returns the form groups associated with a form.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function groups(){
        return $this->hasMany('App\FormGroup', 'fid');
    }

    public function revisions(){
        return $this->hasMany('App\Revision','fid');
    }
}
