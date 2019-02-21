<?php namespace App\KoraFields;

use App\Form;
use App\Http\Controllers\FieldController;
use App\Http\Controllers\RecordController;
use App\Record;
use App\Search;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;

class AssociatorField extends BaseField {

    /*
    |--------------------------------------------------------------------------
    | Associator Field
    |--------------------------------------------------------------------------
    |
    | This model represents the text field in Kora3
    |
    */

    /**
     * @var string - Views for the typed field options
     */
    const FIELD_OPTIONS_VIEW = "partials.fields.options.associator";
    const FIELD_ADV_OPTIONS_VIEW = "partials.fields.advanced.associator";
    const FIELD_ADV_INPUT_VIEW = "partials.records.advanced.associator"; //TODO::CASTLE
    const FIELD_INPUT_VIEW = "partials.records.input.associator";
    const FIELD_DISPLAY_VIEW = "partials.records.display.associator";

    /**
     * Get the field options view.
     *
     * @return string - The view
     */
    public function getFieldOptionsView() {
        return self::FIELD_OPTIONS_VIEW;
    }

    /**
     * Get the field options view for advanced field creation.
     *
     * @return string - The view
     */
    public function getAdvancedFieldOptionsView() {
        return self::FIELD_ADV_OPTIONS_VIEW;
    }

    /**
     * Get the field input view for advanced field search.
     *
     * @return string - The view
     */
    public function getAdvancedSearchInputView() {
        return self::FIELD_ADV_INPUT_VIEW;
    }

    /**
     * Get the field input view for record creation.
     *
     * @return string - The view
     */
    public function getFieldInputView() {
        return self::FIELD_INPUT_VIEW;
    }

    /**
     * Get the field input view for record creation.
     *
     * @return string - The view
     */
    public function getFieldDisplayView() {
        return self::FIELD_DISPLAY_VIEW;
    }

    /**
     * Gets the default options string for a new field.
     *
     * @param  int $fid - Form ID
     * @param  string $slug - Name of database column based on field internal name
     * @param  array $options - Extra information we may need to set up about the field
     * @return array - The default options
     */
    public function addDatabaseColumn($fid, $slug, $options = null) {
        $table = new \CreateRecordsTable();
        $table->addJSONColumn($fid, $slug);
    }

    /**
     * Gets the default options string for a new field.
     *
     * @return array - The default options
     */
    public function getDefaultOptions() {
        return ['SearchForms' => array()];
    }

    /**
     * Update the options for a field
     *
     * @param  array $field - Field to update options
     * @param  Request $request
     * @param  int $flid - The field internal name
     * @return array - The updated field array
     */
    public function updateOptions($field, Request $request, $flid = null) {
        $searchForms = array();
        foreach($request->all() as $key=>$value) {
            if(substr( $key, 0, 8 ) === "checkbox") {
                $fid = explode('_',$key)[1];
                $preview = $request->input("preview_".$fid);
                $val = [
                    'form_id' => $fid,
                    'flids' => $preview,
                    'search' => 1
                ];

                array_push($searchForms,$val);
            }
        }

        $field['default'] = $request->default;
        $field['options']['SearchForms'] = $searchForms;

        return $field;
    }

    /**
     * Validates the record data for a field against the field's options.
     *
     * @param  int $flid - The field internal name
     * @param  array $field - The field data array to validate
     * @param  Request $request
     * @param  bool $forceReq - Do we want to force a required value even if the field itself is not required?
     * @return array - Array of errors
     */
    public function validateField($flid, $field, $request, $forceReq = false) {
        $req = $field['required'];
        $value = $request->{$flid};

        if(($req==1 | $forceReq) && ($value==null | $value==""))
            return [$flid.'_chosen' => $field['name'].' is required'];

        return array();
    }

    /**
     * Formats data for record entry.
     *
     * @param  array $field - The field to represent record data
     * @param  string $value - Data to add
     * @param  Request $request
     *
     * @return mixed - Processed data
     */
    public function processRecordData($field, $value, $request) {
        if(empty($value))
            $value = null;
        return json_encode($value);
    }

    /**
     * Formats data for revision display.
     *
     * @param  mixed $data - The data to store
     * @param  Request $request
     *
     * @return mixed - Processed data
     */
    public function processRevisionData($data) {
        $data = json_decode($data,true);
        $return = '';
        foreach($data as $record) {
            $return .= "<div>".$record."</div>";
        }

        return $return;
    }

    /**
     * Formats data for record entry.
     *
     * @param  string $flid - Field ID
     * @param  array $field - The field to represent record data
     * @param  array $value - Data to add
     * @param  Request $request
     *
     * @return Request - Processed data
     */
    public function processImportData($flid, $field, $value, $request) {
        $request[$flid] = $value;

        return $request;
    }

    /**
     * Formats data for record entry.
     *
     * @param  string $flid - Field ID
     * @param  array $field - The field to represent record data
     * @param  \SimpleXMLElement $value - Data to add
     * @param  Request $request
     * @param  bool $simple - Is this a simple xml field value
     *
     * @return Request - Processed data
     */
    public function processImportDataXML($flid, $field, $value, $request, $simple = false) {
        $request[$flid] = (array)$value->Record;

        return $request;
    }

    /**
     * Formats data for record display.
     *
     * @param  array $field - The field to represent record data
     * @param  string $value - Data to display
     *
     * @return mixed - Processed data
     */
    public function processDisplayData($field, $value) {
        return json_decode($value,true);
    }

    /**
     * Formats data for XML record display.
     *
     * @param  string $field - Field ID
     * @param  string $value - Data to format
     *
     * @return mixed - Processed data
     */
    public function processXMLData($field, $value) {
        $recs = json_decode($value,true);
        $xml = "<$field>";
        foreach($recs as $rec) {
            $xml .= '<Record>'.$rec.'</Record>';
        }
        $xml .= "</$field>";

        return $xml;
    }

    /**
     * Formats data for XML record display.
     *
     * @param  string $value - Data to format
     *
     * @return mixed - Processed data
     */
    public function processLegacyData($value) {
        return $value;
    }

    /**
     * Takes data from a mass assignment operation and applies it to an individual field.
     *
     * @param  Form $form - Form model
     * @param  string $flid - Field ID
     * @param  String $formFieldValue - The value to be assigned
     * @param  Request $request
     * @param  bool $overwrite - Overwrite if data exists
     */
    public function massAssignRecordField($form, $flid, $formFieldValue, $request, $overwrite=0) {
        $recModel = new Record(array(),$form->id);
        if($overwrite)
            $recModel->newQuery()->update([$flid => $formFieldValue]);
        else
            $recModel->newQuery()->whereNull($flid)->update([$flid => $formFieldValue]);
    }

    /**
     * For a test record, add test data to field.
     *
     * @param  string $url - Url for File Type Fields
     * @return mixed - The data
     */
    public function getTestData($url = null) {
        return json_encode(array('0-3-0','0-3-1','0-3-2','0-3-3'));
    }

    /**
     * Provides an example of the field's structure in an export to help with importing records.
     *
     * @param  string $slug - Field nickname
     * @param  string $expType - Type of export
     * @return mixed - The example
     */
    public function getExportSample($slug,$type) {
        switch($type) {
            case "XML":
                $xml = '<' . $slug . '>';
                $xml .= "<Record>".utf8_encode('0-3-0')."</Record>";
                $xml .= "<Record>".utf8_encode('0-3-1')."</Record>";
                $xml .= "<Record>".utf8_encode('0-3-2')."</Record>";
                $xml .= "<Record>".utf8_encode('0-3-3')."</Record>";
                $xml .= '</' . $slug . '>';

                return $xml;
                break;
            case "JSON":
                $fieldArray[$slug] = array('0-3-0','0-3-1','0-3-2','0-3-3');

                return $fieldArray;
                break;
        }
    }

    /**
     * Performs a keyword search on this field and returns any results.
     *
     * @param  string $flid - Field ID
     * @param  string $arg - The keywords
     * @param  Record $recordMod - Model to search through
     * @param  boolean $negative - Get opposite results of the search
     * @return array - The RIDs that match search
     */
    public function keywordSearchTyped($flid, $arg, $recordMod, $negative = false) {
        if($negative)
            $param = 'NOT LIKE';
        else
            $param = 'LIKE';

        return $recordMod->newQuery()
            ->select("id")
            ->where($flid, $param,"%$arg%")
            ->pluck('id')
            ->toArray();
    }

    /**
     * Updates the request for an API search to mimic the advanced search structure.
     *
     * @param  array $data - Data from the search
     * @param  int $flid - Field ID
     * @param  Request $request
     * @return Request - The update request
     */
    public function setRestfulAdvSearch($data, $flid, $request) {
        $request->request->add([$flid.'_input' => $data->value]);

        return $request;
    }

    /**
     * Build the advanced query for a text field.
     *
     * @param  $flid, field id
     * @param  $query, contents of query.
     * @param  Record $recordMod - Model to search through
     * @param  boolean $negative - Get opposite results of the search
     * @return array - The RIDs that match search
     */
    public function advancedSearchTyped($flid, $query, $recordMod, $negative = false) {
        $inputs = $query[$flid . "_input"];

        if($negative)
            $param = 'NOT LIKE';
        else
            $param = 'LIKE';

        $dbQuery = $recordMod->newQuery()
            ->select("id");

        $dbQuery->where(function($dbQuery) use ($flid, $param, $inputs) {
            foreach($inputs as $arg) {
                $dbQuery->where($flid, $param, "%$arg%");
            }
        });

        return $dbQuery->pluck('id')
            ->toArray();
    }

    ///////////////////////////////////////////////END ABSTRACT FUNCTIONS///////////////////////////////////////////////

    /**
     * For a record that was searched for in an associator, grab the data of the field that was assigned as a preview
     * for this record.
     *
     * @param  array $field - Field info array
     * @param  int $kid - Record Kora ID
     * @return string - Html structure of the preview field's value
     */
    public static function getPreviewValues($field,$kid) {
        //individual kid elements
        $recParts = explode('-',$kid);
        $pid = $recParts[0];
        $fid = $recParts[1];
        $rid = $recParts[2];

        $recModel = RecordController::getRecord($kid);
        if(is_null($recModel))
            return '';

        //get the preview flid structure of this associator
        $activeForms = array();
        $options = $field['options']['SearchForms'];
        foreach($options as $opt) {
            $opt_fid = $opt['form_id'];
            $opt_search = $opt['search'];
            $opt_flids = $opt['flids'];
            $opt_flids = explode('-',$opt_flids);

            if($opt_search == 1)
                $flids = array();

            foreach($opt_flids as $flid) {
                //Make sure there actually is a preview field
                if($flid=="")
                    continue;
                $field = FieldController::getField($flid,$opt_fid);
                $flids[$flid] = $field;
            }
            $activeForms[$opt_fid] = ['fields' => $flids];
        }

        //grab the preview fields associated with the form of this kid
        //make sure one is selected first
        $preview = array();
        $prevField = array();
        if(isset($activeForms[$fid])) {
            $details = $activeForms[$fid];
            foreach($details['fields'] as $flid => $field) {
                array_push($prevField, $field['name']);
                if(!in_array($field['type'],Form::$validAssocFields)) {
                    array_push($preview, "Invalid Preview Field");
                } else {
                    $value = $recModel->{$flid};
                    if(is_null($value))
                        $value = "Preview Field Empty";
                    array_push($preview, $value);
                }
            }
        } else {
            array_push($preview, "No Preview Field Available");
        }

        $html = "<div class='header'><a class='mt-xxxs documents-link underline-middle-hover' href='".url("projects/".$pid."/forms/".$fid."/records/".$rid)."'>".$kid."</a><div class='card-toggle-wrap'><a class='card-toggle assoc-card-toggle-js'><i class='icon icon-chevron active'></i></a></div></div><div class='body'><div class='overlay'></div>";

        foreach($preview as $i=>$val) {
            if(isset($prevField[$i]))
                $html .= "<div>".$prevField[$i]."</div><div>".$val."</div>";
            else
                $html .= "<div>".$val."</div>";
        }

        $html .= "</div>";

        return $html;
    }
}