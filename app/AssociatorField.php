<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class AssociatorField extends BaseField {

    protected $fillable = [
        'rid',
        'flid',
        'records'
    ];

    public function keywordSearch(array $args, $partial) {
        // TODO: Implement keywordSearch() method.
    }

    public function keywordSearchQuery($arg) {
        // TODO: Implement keywordSearchQuery() method.
    }
}
