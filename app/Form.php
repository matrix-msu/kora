<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
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
        FormGroup::where("fid", "=", $this->fid)->delete();

        $records = Record::where("fid", "=", $this->fid)->get();
        $fields = Field::where("fid", "=", $this->fid)->get();

        foreach($records as $record) {
            $record->delete();
        }

        foreach($fields as $field) {
            $field->delete();
        }

        parent::delete();
    }

    public static function slugExists($slug){
        $form = Form::where('slug','=',$slug)->get()->first();
        if(is_null($form))
            return false;
        else
            return true;
    }
}
