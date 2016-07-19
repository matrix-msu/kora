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

    public function isMetafiable() {
        // TODO: Implement isMetafiable() method.
        return false; // I think this will never need to be metafied.
    }

    public function toMetadata(Field $field) {
        // TODO: Implement toMetadata() method.
        return null;
    }
}
