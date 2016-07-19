<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class PlaylistField extends FileTypeField  {

    protected $fillable = [
        'rid',
        'flid',
        'audio'
    ];

    /**
     * Pass the fields file array to the files to metadata method.
     *
     * @param Field $field, unneeded.
     * @return array
     */
    public function toMetadata(Field $field) {
        return self::filesToMetadata(explode("[!]", $this->audio));
    }
}