<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class ModelField extends FileTypeField  {

    protected $fillable = [
        'rid',
        'flid',
        'model'
    ];
}
