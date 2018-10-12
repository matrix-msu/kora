<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Form extends Model {

    /*
    |--------------------------------------------------------------------------
    | Form
    |--------------------------------------------------------------------------
    |
    | This model represents the data for a Form
    |
    */

    /**
     * @var array - Attributes that can be mass assigned to model
     */
    protected $fillable = [
        'pid',
        'name',
        'slug',
        'description',
        'public_metadata'
    ];

    /**
     * @var string - Database column that represents the primary key
     */
    protected $primaryKey = "fid";

    /**
     * Returns the project associated with a form.
     *
     * @return BelongsTo
     */
    public function project() {
        return $this->belongsTo('App\Project', 'pid');
    }

    /**
     * Returns the fields associated with a form.
     *
     * @return HasMany
     */
    public function fields() {
        return $this->hasMany('App\Field', 'fid');
    }

    /**
     * Returns the pages associated with a form.
     *
     * @return HasMany
     */
    public function pages() {
        return $this->hasMany('App\Page', 'fid')->orderBy('sequence');
    }

    /**
     * Returns the records associated with a form.
     *
     * @return HasMany
     */
    public function records() {
        return $this->hasMany('App\Record', 'fid');
    }

    /**
     * Returns the form's admin group.
     *
     * @return BelongsTo
     */
    public function adminGroup() {
        return $this->belongsTo('App\FormGroup', 'adminGID');
    }

    /**
     * Returns the form groups associated with a form.
     *
     * @return HasMany
     */
    public function groups() {
        return $this->hasMany('App\FormGroup', 'fid');
    }

    /**
     * Returns the record revisions associated with a form.
     *
     * @return HasMany
     */
    public function revisions() {
        return $this->hasMany('App\Revision','fid');
    }

    /**
     * Deletes all data belonging to the form, then deletes self.
     */
    public function delete() {
        DB::table("record_presets")->where("fid", "=", $this->fid)->delete();
        DB::table("associations")->where("dataForm", "=", $this->fid)->orWhere("assocForm", "=", $this->fid)->delete();
        DB::table("revisions")->where("fid", "=", $this->fid)->delete();

        FormGroup::where("fid", "=", $this->fid)->delete();

        //Replicate the individual record delete but for all form recrods
        Record::where("fid", "=", $this->fid)->delete();
        foreach(BaseField::$MAPPED_FIELD_TYPES as $table_name) {
            DB::table($table_name)->where("fid", "=", $this->fid)->delete();
        }

        // Delete support tables.
        foreach(BaseField::$MAPPED_SUPPORT_FIELD_TYPES as $support_table) {
            DB::table($support_table)->where("fid", "=", $this->fid)->delete();
        }

        $pages = Page::where("fid", "=", $this->fid)->get();
        foreach($pages as $page) {
            $page->delete();
        }

        parent::delete();
    }

    /**
     * Checks if slug is already used by another form.
     *
     * @param  string $slug - Slug to evaluate
     * @return bool - Does exist
     */
    public static function slugExists($slug) {
        $form = self::where('slug', '=', $slug)->get()->first();
        if(is_null($form))
            return false;
        else
            return true;
    }
}
