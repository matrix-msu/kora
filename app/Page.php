<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $fillable = [
        'parent_type',
        'fid',
        'page_id',
        'title',
        'sequence'
    ];

    /**
     * Returns the fields for a given page
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function fields(){
        return $this->hasMany('App\Field', 'page_id')->orderBy('sequence');
    }

    /**
     * Because the MyISAM engine doesn't support foreign keys we have to emulate cascading.
     */
    public function delete() {
        //TODO:: eventually we will delete sub pages within this page
        $fields = Field::where("page_id", "=", $this->id)->get();

        foreach($fields as $field) {
            $field->delete();
        }

        parent::delete();
    }
}
