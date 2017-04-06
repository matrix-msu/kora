<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

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

    /**
     * @param null $field
     * @return string
     */
    public function getRevisionData($field = null) {
        return $this->audio;
    }

    /**
     * Rollback a playlist field based on a revision.
     *
     * @param Revision $revision
     * @param Field $field
     * @return PlaylistField
     */
    public static function rollback(Revision $revision, Field $field) {
        $playlistfield = PlaylistField::where("flid", "=", $field->flid)->where("rid", "=", $revision->rid)->first();

        if (!is_array($revision->data)) {
            $revision->data = json_decode($revision->data, true);
        }

        // If the field doesn't exist or was explicitly deleted, we create a new one.
        if ($revision->type == Revision::DELETE || is_null($playlistfield)) {
            $playlistfield = new PlaylistField();
            $playlistfield->flid = $field->flid;
            $playlistfield->fid = $revision->fid;
            $playlistfield->rid = $revision->rid;
        }

        $playlistfield->audio = $revision->data[Field::_PLAYLIST][$field->flid];
        $playlistfield->save();

        return $playlistfield;
    }

    /**
     * Build the advanced search query.
     *
     * @param $flid
     * @param $query
     * @return Builder
     */
    public static function getAdvancedSearchQuery($flid, $query) {
        $processed = self::processAdvancedSearchInput($query[$flid."_input"]);

        return DB::table("playlist_fields")
            ->select("rid")
            ->where("flid", "=", $flid)
            ->whereRaw("MATCH (`audio`) AGAINST (? IN BOOLEAN MODE)", [$processed])
            ->distinct();
    }
}