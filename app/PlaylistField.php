<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class PlaylistField extends FileTypeField  {

    protected $fillable = [
        'rid',
        'flid',
        'audio'
    ];

}
