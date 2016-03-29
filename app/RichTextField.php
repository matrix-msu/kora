<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class RichTextField extends BaseField {

    protected $fillable = [
        'rid',
        'flid',
        'rawtext'
    ];

    public function keywordSearch(array &$args, $partial)
    {
        // TODO: Implement keyword_search() method.
    }
}
