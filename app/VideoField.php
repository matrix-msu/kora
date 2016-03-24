<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class VideoField extends BaseField {

    protected $fillable = [
        'rid',
        'flid',
        'video'
    ];

    public function keyword_search(array &$args, $partial)
    {
        // TODO: Implement keyword_search() method.
    }
}
