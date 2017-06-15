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
     * Because the MyISAM engine doesn't support foreign keys we have to emulate cascading.
     */
    public function delete() {
        //TODO:: eventually we will delete fields and sub pages within this page

        parent::delete();
    }
}
