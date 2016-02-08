<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class ComboListField extends Model {

    protected $fillable = [
        'rid',
        'flid',
        'options',
        'ftype1',
        'ftype2'
    ];

    protected $primaryKey = "id";

    public function record(){
        return $this->belongsTo('App\Record');
    }

    public static function getComboList($field, $blankOpt=false, $fnum)
    {
        $dbOpt = ComboListField::getComboFieldOption($field, 'Options', $fnum);
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

    public static function getComboFieldOption($field, $key, $num){
        $options = $field->options;
        if($num=='one')
            $opt = explode('[!Field1!]',$options)[1];
        else if($num=='two')
            $opt = explode('[!Field2!]',$options)[1];

        $tag = '[!'.$key.'!]';
        $value = explode($tag,$opt)[1];

        return $value;
    }

    public static function getComboFieldName($field, $num){
        $options = $field->options;

        if($num=='one') {
            $oneOpts = explode('[!Field1!]', $options)[1];
            $name = explode('[Name]', $oneOpts)[1];
        }else if ($num=='two') {
            $twoOpts = explode('[!Field2!]', $options)[1];
            $name = explode('[Name]', $twoOpts)[1];
        }

        return $name;
    }

    public static function getComboFieldType($field, $num){
        $options = $field->options;

        if($num=='one') {
            $oneOpts = explode('[!Field1!]', $options)[1];
            $type = explode('[Type]', $oneOpts)[1];
        }else if ($num=='two') {
            $twoOpts = explode('[!Field2!]', $options)[1];
            $type = explode('[Type]', $twoOpts)[1];
        }

        return $type;
    }

}
