<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class PlaylistField extends BaseField {

    protected $fillable = [
        'rid',
        'flid',
        'audio'
    ];

    public function keyword_search(array &$args, $partial)
    {
        // TODO: Implement keyword_search() method.
    }
}
