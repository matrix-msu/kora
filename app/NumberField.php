<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class NumberField extends BaseField {

    protected $fillable = [
        'rid',
        'flid',
        'number'
    ];

    public function keywordSearch(array &$args, $partial)
    {
        // TODO: Implement keyword_search() method.
    }
}
