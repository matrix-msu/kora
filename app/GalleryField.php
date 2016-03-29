<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class GalleryField extends BaseField {

    protected $fillable = [
        'rid',
        'flid',
        'images'
    ];

    public function keywordSearch(array &$args, $partial)
    {
        // TODO: Implement keyword_search() method.
    }
}
