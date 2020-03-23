<?php namespace App\KoraFields;

use App\Form;
use App\Http\Controllers\FieldController;
use App\Http\Controllers\RecordController;
use App\Record;
use App\Search;
use Illuminate\Http\Request;

class AssociatorField extends BaseField {

    /*
    |--------------------------------------------------------------------------
    | Associator Field
    |--------------------------------------------------------------------------
    |
    | This model represents the text field in kora
    |
    */

    /**
     * @var string - Name of cache table
     */
    const Reverse_Cache_Table = "reverse_associator_cache";
    const Reverse_Temp_Table = "reverse_associator_temp";

    /**
     * @var string - Views for the typed field options
     */
    const FIELD_OPTIONS_VIEW = "partials.fields.options.associator";
    const FIELD_ADV_OPTIONS_VIEW = "partials.fields.advanced.associator";
    const FIELD_ADV_INPUT_VIEW = "partials.records.advanced.associator";
    const FIELD_INPUT_VIEW = "partials.records.input.associator";
    const FIELD_DISPLAY_VIEW = "partials.records.display.associator";

    /**
     * @var string - Method from CreateRecordsTable() for adding to DB
     */
    const FIELD_DATABASE_METHOD = 'addJSONColumn';

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
     * @return array - The default options
     */
    public function getDefaultOptions($type = null) {
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
    public function updateOptions($field, Request $request, $flid = null, $prefix = 'records_') {
        $searchForms = array();
        foreach($request->all() as $key=>$value) {
            if(substr( $key, 0, 8 ) === "checkbox") {
                $fid = explode('_',$key)[1];
                $preview = $request->input("preview_".$fid);
                $val = [
                    'form_id' => $fid,
                    'flids' => $preview
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
        if(empty($value)) {
            $value = null;
        } elseif(is_string($value)) {
            $value = explode(' | ', $value);
        }

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
     *
     * @return Request - Processed data
     */
    public function processImportDataXML($flid, $field, $value, $request) {
        $request[$flid] = (array)$value->record;

        return $request;
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
    public function processImportDataCSV($flid, $field, $value, $request) {
        $cleaned = array();
        $values = explode('|', $value);
        foreach($values as $val) {
            $cleaned[] = trim($val);
        }
        $request[$flid] = $cleaned;

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
     * @param  int $fid - Form ID
     *
     * @return mixed - Processed data
     */
    public function processXMLData($field, $value, $fid = null) {
        $recs = json_decode($value,true);
        $xml = "<$field>";
        foreach($recs as $rec) {
            $xml .= '<record>'.$rec.'</record>';
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
        return json_decode($value,true);
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
     * Takes data from a mass assignment operation and applies it to an individual field for a set of records.
     *
     * @param  Form $form - Form model
     * @param  string $flid - Field ID
     * @param  String $formFieldValue - The value to be assigned
     * @param  Request $request
     * @param  array $kids - The KIDs to update
     */
    public function massAssignSubsetRecordField($form, $flid, $formFieldValue, $request, $kids) {
        $recModel = new Record(array(),$form->id);
        $recModel->newQuery()->whereIn('kid',$kids)->update([$flid => $formFieldValue]);
    }

    /**
     * Performs a keyword search on this field and returns any results.
     *
     * @param  array $flids - Field ID
     * @param  string $arg - The keywords
     * @param  Record $recordMod - Model to search through
     * @param  boolean $negative - Get opposite results of the search
     * @return array - The RIDs that match search
     */
    public function keywordSearchTyped($flids, $arg, $recordMod, $form, $negative = false) {
        if($negative)
            $param = 'NOT LIKE';
        else
            $param = 'LIKE';

        $query = $recordMod->newQuery()
            ->select("id");

        foreach($flids as $f) {
            $query = $query->orWhere($f, $param,"$arg");
        }

        return $query->pluck('id')
            ->toArray();
    }

    /**
     * Updates the request for an API search to mimic the advanced search structure.
     *
     * @param  array $data - Data from the search
     * @return array - The update request
     */
    public function setRestfulAdvSearch($data) {
        $request = [];

        if(isset($data->input) && is_array($data->input))
            $request['input'] = $data->input;

        $request['any'] = (isset($data->any) && is_bool($data->any)) ? $data->any : false;

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
    public function advancedSearchTyped($flid, $query, $recordMod, $form, $negative = false) {
        $arg = $query['input'];
        $any = $query['any'];
        $args = Search::prepare($arg);

        $query = $recordMod->newQuery()
            ->select("id");

        if($negative && !$any) {
            foreach($args as $a) {
                $query->orWhereRaw("JSON_SEARCH(`$flid`,'one','$a') IS NULL");
            }
        } else if(!$negative && !$any) {
            foreach($args as $a) {
                $query->whereRaw("JSON_SEARCH(`$flid`,'one','$a') IS NOT NULL");
            }
        } else if($negative && $any) {
            foreach($args as $a) {
                $query->whereRaw("JSON_SEARCH(`$flid`,'one','$a') IS NULL");
            }
        } else if(!$negative && $any) {
            foreach($args as $a) {
                $query->orWhereRaw("JSON_SEARCH(`$flid`,'one','$a') IS NOT NULL");
            }
        }

        return $query->pluck('id')
            ->toArray();
    }

    ///////////////////////////////////////////////END ABSTRACT FUNCTIONS///////////////////////////////////////////////

    /**
     * For a record that was searched for in an associator, grab the data of the field that was assigned as a preview
     * for this record.
     *
     * @param  array $field - Field info array
     * @param  int $kid - Record kora ID
     * @return string - Html structure of the preview field's value
     */
    public static function getPreviewValues($field,$kid) {
        if(!Record::isKIDPattern($kid))
            return '';

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
            $opt_flids = $opt['flids'];

            $flids = [];
            if(!is_null($opt_flids)) {
                foreach($opt_flids as $flid) {
                    //Make sure there actually is a preview field
                    if($flid=="")
                        continue;
                    $field = FieldController::getField($flid,$opt_fid);
                    $flids[$flid] = $field;
                }
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

        $html = "<div class='header'><a class='mt-xxxs associator-link underline-middle-hover' href='".url("projects/".$pid."/forms/".$fid."/records/".$rid)."'>".$kid."</a><div class='card-toggle-wrap'><a class='card-toggle assoc-card-toggle-js'><i class='icon icon-chevron active'></i></a></div></div><div class='body'><div class='overlay'></div>";

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
