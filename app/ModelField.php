<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class ModelField extends BaseField {

    protected $fillable = [
        'rid',
        'flid',
        'model'
    ];

    public function keywordSearch(array $args, $partial)
    {
        // TODO: Implement keyword_search() method.
    }
}
