<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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

    /**
     * Because the MyISAM engine doesn't support foreign keys we have to emulate cascading.
     */
    public function delete() {
        DB::table("record_presets")->where("fid", "=", $this->fid)->delete();
        DB::table("associations")->where("dataForm", "=", $this->fid)->orWhere("assocForm", "=", $this->fid)->delete();
        DB::table("revisions")->where("fid", "=", $this->fid)->delete();

        $to_delete = Record::where("fid", "=", $this->fid)->get();
        $to_delete = $to_delete->merge(Field::where("fid", "=", $this->fid)->get());

        foreach($to_delete as $delete_me) {
            $delete_me->delete();
        }

        parent::delete();
    }
}
