<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class ComboListField extends BaseField {

    protected $fillable = [
        'rid',
        'flid',
        'options',
        'ftype1',
        'ftype2'
    ];

    /**
     * Keyword search for a combo list field.
     * This search simply uses the already existing search functions of text, number, list, multi-select list, and generated list.
     *
     *
     * @param array $args, array of arguments for the search to use.
     * @param bool $partial, true if partial values should be considered.
     * @return bool, true if the search found something, false otherwise.
     */
    public function keywordSearch(array $args, $partial) {
        $field = Field::where('flid', '=', $this->flid)->first();
        $type1 = self::getComboFieldType($field, 'one');
        $type2 = self::getComboFieldType($field, 'two');

        $f1vals = explode("[!f1!]", $this->options);
        $f2vals = explode("[!f2!]", $this->options);

        //
        // Iterate through the field values and search them.
        //
        for ($i = 1; $i < count($f1vals); $i += 2) { // Every other value in the array will hold what we're interested in.
            $field = self::makeTempField($type1, $f1vals[$i]);
            if ($field->keywordSearch($args, $partial) == true) return true;

            $field = self::makeTempField($type2, $f2vals[$i]);
            if ($field->keywordSearch($args, $partial) == true) return true;
        }

        return false; // We didn't find anything.
    }

    /**
     * Creates a temporary field that we can execute keyword search on.
     *
     * @param string $type, the type of the field we are to create.
     * @param string $value, the value associated with the field.
     * @return BaseField, a field that we can execute keyword search on.
     */
    private static function makeTempField($type, $value) {
        switch($type) {
            case "Text":
                $field = new TextField(); // We can just concatenate everything into a string for text.
                $field->text = $value;
                break;

            case "Number":
                $field = new NumberField();
                $field->number = $value;
                break;

            case "List":
                $field = new ListField();
                $field->option = $value;
                break;

            case "Multi-Select List":
                $field = new MultiSelectListField();
                $field->options = $value;
                break;

            case "Generated List":
                $field = new GeneratedListField();
                $field->options = $value;
                break;

            default:
                return null; // Something went wrong.
        }

        return $field;
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
