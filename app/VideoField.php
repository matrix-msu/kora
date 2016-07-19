<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class VideoField extends FileTypeField {

    protected $fillable = [
        'rid',
        'flid',
        'video'
    ];

    /**
     * Pass the fields file array to the files to metadata method.
     *
     * @param Field $field, unneeded.
     * @return array
     */
    public function toMetadata(Field $field) {
        return self::filesToMetadata(explode("[!]", $this->video));
    }
}
