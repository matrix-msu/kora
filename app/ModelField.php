<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class ModelField extends FileTypeField  {

    protected $fillable = [
        'rid',
        'flid',
        'model'
    ];

   public function keywordSearchQuery($query, $arg) {
        // TODO: Implement keywordSearchQuery() method.
    }
}
