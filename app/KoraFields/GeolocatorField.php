<?php namespace App\KoraFields;

use App\Form;
use App\Record;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GeolocatorField extends BaseField {

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
    const FIELD_OPTIONS_VIEW = "partials.fields.options.geolocator";
    const FIELD_ADV_OPTIONS_VIEW = "partials.fields.advanced.geolocator";
    const FIELD_ADV_INPUT_VIEW = "partials.records.advanced.geolocator";
    const FIELD_INPUT_VIEW = "partials.records.input.geolocator";
    const FIELD_DISPLAY_VIEW = "partials.records.display.geolocator";

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
    public function getDefaultOptions($type = null) {
        return ['Map' => 0, 'DataView' => 'LatLon'];
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
        $reqDefs = $request->default;
        $default = [];

        if(!is_null($reqDefs)) {
            foreach($reqDefs as $def) {
                $default[] = json_decode($def,true);
            }
        }

        $field['default'] = $default;
        $field['options']['Map'] = $request->map;
        $field['options']['DataView'] = $request->view;

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

        $toSave = array();
        foreach($value as $loc) {
            //If coming from import or api the inner location arrays are not encoded like record create
            if(is_array($loc))
                array_push($toSave, json_encode($loc));
            else
                array_push($toSave, $loc);
        }

        return '['.implode(',',$toSave).']';
    }

    /**
     * Formats data for revision display.
     *
     * @param  mixed $data - The data to store
     * @param  Request $request
     *
     * @return mixed - Processed data
     */
    public function processRevisionData($data) { //TODO::CASTLE
        return '';
        $data = json_decode($data,true);
        $return = '';
        foreach($data as $location) {
            $return .= "<div>".$location."</div>";
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

        if (is_string($value)) {
            $geo = array();
            $values = explode(' | ', $value);

            foreach ($values as $value) {
                $blob = explode(' [DESCRIPTION] ', $value);
                $loc = $description = '';
                $geoReq = new Request();

                if (count($blob) == 2) {
                    list($loc, $description) = $blob;
                } else {
                    $loc = $blob[0];
                }

                list($lat, $lon) = array_merge(explode(',', $loc), array(''));

                if (is_numeric($lat) && is_numeric($lon)) {
                    $geoReq->type = 'latlon';
                    $geoReq->lat = $lat;
                    $geoReq->lon = $lon;
                } else {
                    $geoReq->type = 'geo';
                    $geoReq->addr = $loc;
                }

                $loc = GeolocatorField::geoConvert($geoReq);
                $loc['description'] = $description;
                array_push($geo, $loc);
            }

            $request[$flid] = $geo;
        }

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
        $geo = array();

        foreach($value->Location as $loc) {
            $geoReq = new Request();

            if(!is_null($loc->Lat)) {
                $geoReq->type = 'latlon';
                $geoReq->lat = (float)$loc->Lat;
                $geoReq->lon = (float)$loc->Lon;
            } else if(!is_null($loc->Address)) {
                $geoReq->type = 'geo';
                $geoReq->addr = (string)$loc->Address;
            }


            $loc = GeolocatorField::geoConvert($geoReq);
            if(empty($loc->Desc))
                $loc['description'] = '';
            else
                $loc['description'] = $loc->Desc;
            array_push($geo, $loc);
        }

        $request[$flid] = $geo;

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
        $locs = json_decode($value,true);
        $xml = "<$field>";
        foreach($locs as $loc) {
            $xml .= '<Desc>'.$loc['description'].'</Desc>';
            $xml .= '<Lat>'.$loc['geometry']['location']['lat'].'</Lat>';
            $xml .= '<Lon>'.$loc['geometry']['location']['lng'].'</Lon>';
            $xml .= '<Address>'.$loc['formatted_address'].'</Address>';
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
        return null;
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
        $locsValue = '['.implode(',',$formFieldValue).']';
        $recModel = new Record(array(),$form->id);
        if($overwrite)
            $recModel->newQuery()->update([$flid => $locsValue]);
        else
            $recModel->newQuery()->whereNull($flid)->update([$flid => $locsValue]);
    }

    /**
     * For a test record, add test data to field.
     *
     * @param  string $url - Url for File Type Fields
     * @return mixed - The data
     */
    public function getTestData($url = null) {
        $locArray = [];
        $locArray['description'] = 'Matrix';
        $locArray['geometry']['location']['lat'] = 42.7314094;
        $locArray['geometry']['location']['lng'] = -84.476258;
        $locArray['formatted_address'] = '288 Farm Ln, East Lansing, MI 48823';
        return json_encode(array($locArray));
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
                $xml .= '<Location>';
                $xml .= '<Desc>' . utf8_encode('Matrix') . '</Desc>';
                $xml .= '<Lat>' . utf8_encode('42.7314094') . '</Lat>';
                $xml .= '<Lon>' . utf8_encode('-84.476258') . '</Lon>';
                $xml .= '<Address>' . utf8_encode('288 Farm Ln, East Lansing, MI 48823') . '</Address>';
                $xml .= '</Location>';
                $xml .= '</' . $slug . '>';

                return $xml;
                break;
            case "JSON":
                $fieldArray = [$slug => ['type' => 'Geolocator']];

                $locArray = array();

                $locArray['description'] = 'Matrix';
                $locArray['geometry']['location']['lat'] = 42.7314094;
                $locArray['geometry']['location']['lng'] = -84.476258;
                $locArray['formatted_address'] = '288 Farm Ln, East Lansing, MI 48823';
                $fieldArray[$slug] = array($locArray);

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

        $dbQuery = $recordMod->newQuery()
            ->select("id");

        if($negative) {
            $dbQuery->whereRaw("`$flid`->\"$[*].formatted_address\" $param \"$arg\"");
            $dbQuery->whereRaw("`$flid`->\"$[*].description\" $param \"$arg\"");
        } else {
            $dbQuery->orWhereRaw("`$flid`->\"$[*].formatted_address\" $param \"$arg\"");
            $dbQuery->orWhereRaw("`$flid`->\"$[*].description\" $param \"$arg\"");
        }

        return $dbQuery->pluck('id')
            ->toArray();
    }

    /**
     * Updates the request for an API search to mimic the advanced search structure.
     *
     * @param  array $data - Data from the search
     * @return array - The update request
     */
    public function setRestfulAdvSearch($data) {
        $return = [];

        if(isset($data->lat) && is_double($data->lat))
            $return['lat'] = $data->lat;
        else
            $return['lat'] = '';

        if(isset($data->lng) && is_double($data->lng))
            $return['lng'] = $data->lng;
        else
            $return['lng'] = '';

        if(isset($data->range) && is_int($data->range))
            $return['range'] = $data->range;
        else
            $return['range'] = '';

        return $return;
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
        $lat = (double)$query['lat'];
        $lng = (double)$query['lng'];
        $range = (int)$query['range'];

        if($negative)
            $param = '>';
        else
            $param = '<';

        //This function determines if a single LatLon point is in range of a set of LatLon coordinates
        DB::unprepared("DROP FUNCTION IF EXISTS `inRange`;
            CREATE FUNCTION `inRange`(`lats` JSON,`lngs` JSON,`range` INT, rangeLat DOUBLE, rangeLng DOUBLE)
            RETURNS BOOL
            BEGIN
                DECLARE i INT DEFAULT 0;
                DECLARE result BOOL DEFAULT false;
                DECLARE providedLat DOUBLE;
                DECLARE providedLng DOUBLE;

                WHILE i < JSON_LENGTH(`lats`) DO
                    SELECT JSON_EXTRACT(`lats`,CONCAT('$[',i,']')) INTO providedLat;
                    SELECT JSON_EXTRACT(`lngs`,CONCAT('$[',i,']')) INTO providedLng;
                    IF (6371 * acos(cos(radians(rangeLat)) * cos(radians(providedLat)) * cos(radians(providedLng) - radians(rangeLng)) + sin(radians(rangeLat)) * sin( radians(providedLat)))) $param `range` THEN SET result = TRUE;
                    END IF;
                    SET i = i+1;
                END WHILE;

                RETURN result;
            END;");

        $dbQuery = $recordMod->newQuery()
            ->select("id")
            ->whereRaw("inRange(`$flid`->\"$[*].geometry.location.lat\",`$flid`->\"$[*].geometry.location.lng\",?,?,?)")
            ->setBindings([$range, $lat, $lng]);

        return $dbQuery->pluck('id')
            ->toArray();
    }

    ///////////////////////////////////////////////END ABSTRACT FUNCTIONS///////////////////////////////////////////////

    /**
     * Validates the address for a Geolocator field.
     *
     * @param  Request $request
     * @return bool - Result of address validity
     */
    public static function validateAddress(Request $request) {
        $address = $request->address;

        $con = app('geocoder');

        try {
            $con->geocode($address);
        } catch(\Exception $e) {
            return json_encode(false);
        }

        return json_encode(true);
    }

    /**
     * Converts provide lat/long, utm, or geo coordinates into the other types.
     *
     * @param  Request $request
     * @return array - Converted coordinates
     */
    public static function geoConvert(Request $request) {
        $lat = null;
        $lon = null;
        $addr = null;

        switch($request->type) {
            case 'latlon':
                $lat = $request->lat;
                $lon = $request->lon;

                //to address
                $con = app('geocoder');
                try {
                    $res = $con->reverse($lat, $lon)->get()->first();
                    if ($res !== null) {
                        $addr = $res->getDisplayName();
                    } else {
                        $addr = 'Address Not Found';
                    }
                } catch(\Exception $e) {
                    $addr = 'Address Not Found';
                }
                break;
            case 'geo':
                $addr = $request->addr;

                //to latlon
                $con = app('geocoder');
                try {
                    $res = $con->geocode($addr)->get()->first()->getCoordinates();
                    $lat = $res->getLatitude();
                    $lon = $res->getLongitude();
                } catch(\Exception $e) {
                    $lat = null;
                    $lon = null;
                }
                break;
            default:
                break;
        }

        return [
            'geometry' => [
                'location' => [
                    'lat' => $lat,
                    'lng' => $lon
                ]
            ],
            'formatted_address' => $addr
        ];
    }
}
