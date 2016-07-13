<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class GalleryField extends FileTypeField  {

    protected $fillable = [
        'rid',
        'flid',
        'images'
    ];
}
