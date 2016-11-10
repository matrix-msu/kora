<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class ModelField extends FileTypeField  {

    protected $fillable = [
        'rid',
        'flid',
        'model'
    ];

    /**
     * Pass the fields file array to the files to metadata method.
     *
     * @param Field $field, unneeded.
     * @return array
     */
    public function toMetadata(Field $field) {
        return self::filesToMetadata(explode("[!]", $this->model));
    }

    public static function getAdvancedSearchQuery($flid, $query) {
        return FileTypeField::getAdvancedSearchQuery($flid, $query, "model", isset($query[$flid."_extension"]));
    }
}
