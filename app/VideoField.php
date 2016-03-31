<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class VideoField extends BaseField {

    protected $fillable = [
        'rid',
        'flid',
        'video'
    ];

    public function keywordSearch(array $args, $partial)
    {
        // TODO: Implement keyword_search() method.
    }
}
