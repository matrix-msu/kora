<?php namespace App;

use App\Http\Controllers\FieldController;
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

    public static function getDefault($default, $blankOpt=false)
    {
        $options = array();

        if ($default == '') {
            //skip
        } else if (!strstr($default, '[!]')) {
            $options = [$default => $default];
        } else {
            $opts = explode('[!]', $default);
            foreach ($opts as $opt) {
                $options[$opt] = $opt;
            }
        }

        if ($blankOpt) {
            $options = array('' => '') + $options;
        }

        return $options;
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
