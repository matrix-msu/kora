<?php namespace App;

use App\Http\Controllers\FieldController;
use Illuminate\Database\Eloquent\Model;

class GeneratedListField extends Model {

    protected $fillable = [
        'rid',
        'flid',
        'options'
    ];

    protected $primaryKey = "id";

    public function record(){
        return $this->belongsTo('App\Record');
    }

    public static function getList($field, $blankOpt=false)
    {
        $dbOpt = FieldController::getFieldOption($field, 'Options');
        $options = array();

        if ($dbOpt == '') {
            //skip
        } else if (!strstr($dbOpt, '[!]')) {
            $options = [$dbOpt => $dbOpt];
        } else {
            $opts = explode('[!]', $dbOpt);
            foreach ($opts as $opt) {
                $options[$opt] = $opt;
            }
        }

        if ($blankOpt) {
            $options = array('' => '') + $options;
        }

        return $options;
    }

}
