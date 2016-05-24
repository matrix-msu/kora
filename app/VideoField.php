<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class VideoField extends FileTypeField {

    protected $fillable = [
        'rid',
        'flid',
        'video'
    ];
}
