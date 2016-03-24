<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class AssociatorField extends BaseField {

    protected $fillable = [
        'rid',
        'flid',
        'records'
    ];
}
