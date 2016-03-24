<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class ModelField extends BaseField {

    protected $fillable = [
        'rid',
        'flid',
        'model'
    ];

    public function keyword_search(array &$args, $partial)
    {
        // TODO: Implement keyword_search() method.
    }
}
