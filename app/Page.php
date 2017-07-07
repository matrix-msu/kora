<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Page extends Model {

    /*
    |--------------------------------------------------------------------------
    | Page
    |--------------------------------------------------------------------------
    |
    | This model represents a page within the form layout
    |
    */

    /**
     * @var array - Attributes that can be mass assigned to model
     */
    protected $fillable = [
        'parent_type',
        'fid',
        'title',
        'sequence'
    ];

    /**
     * Returns the fields for a given page.
     *
     * @return HasMany
     */
    public function fields(){
        return $this->hasMany('App\Field', 'page_id')->orderBy('sequence');
    }

    /**
     * Delete the fields contained in the page, and then deletes self.
     */
    public function delete() {
        $fields = Field::where("page_id", "=", $this->id)->get();

        foreach($fields as $field) {
            $field->delete();
        }

        parent::delete();
    }
}
