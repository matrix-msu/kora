<?php namespace App;

use App\FieldHelpers\gPoint;
use App\Http\Controllers\FieldController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;

class GeolocatorField extends BaseField {

    /*
    |--------------------------------------------------------------------------
    | Geolocator Field
    |--------------------------------------------------------------------------
    |
    | This model represents the geolocator field in Kora3
    |
    */

    /**
     * @var string - Support table name
     */
    const SUPPORT_NAME = "geolocator_support";
    /**
     * @var string - Views for the typed field options
     */
    const FIELD_OPTIONS_VIEW = "partials.fields.options.geolocator";
    const FIELD_ADV_OPTIONS_VIEW = "partials.fields.advanced.geolocator";
    const FIELD_ADV_INPUT_VIEW = "partials.records.advanced.geolocator";
    const FIELD_INPUT_VIEW = "partials.records.input.geolocator";
    const FIELD_DISPLAY_VIEW = "partials.records.display.geolocator";

    /**
     * @var string - Data column used for sort
     */
    const SORT_COLUMN = null;

    /**
     * @var array - Attributes that can be mass assigned to model
     */
    protected $fillable = [
        'rid',
        'flid',
        'locations'
    ];

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
     * Get the field options view for advanced field creation.
     *
     * @return string - Column name
     */
    public function getSortColumn() {
        return self::SORT_COLUMN;
    }

    /**
     * Gets the default options string for a new field.
     *
     * @param  Request $request
     * @return string - The default options
     */
    public function getDefaultOptions(Request $request) {
        return '[!Map!]No[!Map!][!DataView!]LatLon[!DataView!]';
    }

    /**
     * Gets an array of all the fields options.
     *
     * @param  Field $field
     * @return array - The options array
     */
    public function getOptionsArray(Field $field) {
        $options = array();

        $options['MapView'] = FieldController::getFieldOption($field, 'Map');
        $options['DataView'] = FieldController::getFieldOption($field, 'DataView');

        return $options;
    }

    /**
     * Update the options for a field
     *
     * @param  Field $field - Field to update options
     * @param  Request $request
     * @return Redirect
     */
    public function updateOptions($field, Request $request) {
        $reqDefs = $request->default;
        if(!is_null($reqDefs)) {
            $default = $reqDefs[0];
            for ($i = 1; $i < sizeof($reqDefs); $i++) {
                $default .= '[!]' . $reqDefs[$i];
            }
        } else {
            $default = null;
        }

        $field->updateRequired($request->required);
        $field->updateSearchable($request);
        $field->updateDefault($default);
        $field->updateOptions('Map', $request->map);
        $field->updateOptions('DataView', $request->view);

        return redirect('projects/' . $field->pid . '/forms/' . $field->fid . '/fields/' . $field->flid . '/options')
            ->with('k3_global_success', 'field_options_updated');
    }

    /**
     * Creates a typed field to store record data.
     *
     * @param  Field $field - The field to represent record data
     * @param  Record $record - Record being created
     * @param  string $value - Data to add
     * @param  Request $request
     */
    public function createNewRecordField($field, $record, $value, $request) {
        $this->flid = $field->flid;
        $this->rid = $record->rid;
        $this->fid = $field->fid;
        $this->save();

        $this->addLocations($value);
    }

    /**
     * Edits a typed field that has record data.
     *
     * @param  string $value - Data to add
     * @param  Request $request
     */
    public function editRecordField($value, $request) {
        if(!is_null($this) && !is_null($value)) {
            $this->updateLocations($value);
        } else if(!is_null($this) && is_null($value)) {
            $this->delete();
            $this->deleteLocations();
        }
    }

    /**
     * Takes data from a mass assignment operation and applies it to an individual field.
     *
     * @param  Field $field - The field to represent record data
     * @param  String $formFieldValue - The value to be assigned
     * @param  Request $request
     * @param  bool $overwrite - Overwrite if data exists
     */
    public function massAssignRecordField($field, $formFieldValue, $request, $overwrite=0) {
        //Get array of all RIDs in form
        $rids = Record::where('fid','=',$field->fid)->pluck('rid')->toArray();
        //Get list of RIDs that have the value for that field
        $ridsValue = GeolocatorField::where('flid','=',$field->flid)->pluck('rid')->toArray();
        //Subtract to get RIDs with no value
        $ridsNoVal = array_diff($rids, $ridsValue);

        //Modify Data
        $newData = array();
        foreach($formFieldValue as $location) {
            $newLoc = [];
            $newLoc['desc'] = explode('[Desc]', $location)[1];
            $latlon = explode('[LatLon]', $location)[1];
            $utm = explode('[UTM]', $location)[1];
            $newLoc['address'] = trim(explode('[Address]', $location)[1]);
            $newLoc['lat'] = floatval(explode(',', $latlon)[0]);
            $newLoc['lon'] = floatval(explode(',', $latlon)[1]);
            $utm_arr = explode(':', $utm);
            $newLoc['zone'] = $utm_arr[0];
            $newLoc['easting'] = explode(',', $utm_arr[1])[0];
            $newLoc['northing'] = explode(',', $utm_arr[1])[1];

            array_push($newData, $newLoc);
        }

        foreach(array_chunk($ridsNoVal,1000) as $chunk) {
            //Create data array and store values for no value RIDs
            $fieldArray = [];
            $dataArray = [];
            $now = date("Y-m-d H:i:s");
            foreach($chunk as $rid) {
                $fieldArray[] = [
                    'rid' => $rid,
                    'fid' => $field->fid,
                    'flid' => $field->flid
                ];
                foreach($newData as $loc) {
                    $dataArray[] = [
                        'rid' => $rid,
                        'fid' => $field->fid,
                        'flid' => $field->flid,
                        'desc' => $loc['desc'],
                        'lat' => $loc['lat'],
                        'lon' => $loc['lon'],
                        'zone' => $loc['zone'],
                        'easting' => $loc['easting'],
                        'northing' => $loc['northing'],
                        'address' => $loc['address'],
                        'created_at' => $now,
                        'updated_at' => $now
                    ];
                }
            }
            GeolocatorField::insert($fieldArray);
            DB::table(self::SUPPORT_NAME)->insert($dataArray);
        }

        if($overwrite) {
            foreach(array_chunk($ridsValue,1000) as $chunk) {
                DB::table(self::SUPPORT_NAME)->where('flid', '=', $field->flid)->whereIn('rid', 'in', $ridsValue)->delete();

                $dataArray = [];
                foreach($chunk as $rid) {
                    foreach($newData as $loc) {
                        $dataArray[] = [
                            'rid' => $rid,
                            'fid' => $field->fid,
                            'flid' => $field->flid,
                            'desc' => $loc['desc'],
                            'lat' => $loc['lat'],
                            'lon' => $loc['lon'],
                            'zone' => $loc['zone'],
                            'easting' => $loc['easting'],
                            'northing' => $loc['northing'],
                            'address' => $loc['address'],
                            'created_at' => $now,
                            'updated_at' => $now
                        ];
                    }
                }

                DB::table(self::SUPPORT_NAME)->insert($dataArray);
            }
        }
    }

    /**
     * Takes data from a mass assignment operation and applies it to an individual field for a record subset.
     *
     * @param  Field $field - The field to represent record data
     * @param  String $formFieldValue - The value to be assigned
     * @param  Request $request
     * @param  array $rids - Overwrite if data exists
     */
    public function massAssignSubsetRecordField($field, $formFieldValue, $request, $rids) {
        //Delete the old data
        GeolocatorField::where('flid','=',$field->flid)->whereIn('rid', $rids)->delete();
        DB::table(self::SUPPORT_NAME)->where('flid','=',$field->flid)->whereIn('rid','in', $rids)->delete();

        //Modify Data
        $newData = array();
        foreach($formFieldValue as $location) {
            $newLoc = [];
            $newLoc['desc'] = explode('[Desc]', $location)[1];
            $latlon = explode('[LatLon]', $location)[1];
            $utm = explode('[UTM]', $location)[1];
            $newLoc['address'] = trim(explode('[Address]', $location)[1]);
            $newLoc['lat'] = floatval(explode(',', $latlon)[0]);
            $newLoc['lon'] = floatval(explode(',', $latlon)[1]);
            $utm_arr = explode(':', $utm);
            $newLoc['zone'] = $utm_arr[0];
            $newLoc['easting'] = explode(',', $utm_arr[1])[0];
            $newLoc['northing'] = explode(',', $utm_arr[1])[1];

            array_push($newData, $newLoc);
        }

        foreach(array_chunk($rids,1000) as $chunk) {
            //Create data array and store values for no value RIDs
            $fieldArray = [];
            $dataArray = [];
            $now = date("Y-m-d H:i:s");
            foreach($chunk as $rid) {
                $fieldArray[] = [
                    'rid' => $rid,
                    'fid' => $field->fid,
                    'flid' => $field->flid
                ];
                foreach($newData as $loc) {
                    $dataArray[] = [
                        'rid' => $rid,
                        'fid' => $field->fid,
                        'flid' => $field->flid,
                        'desc' => $loc['desc'],
                        'lat' => $loc['lat'],
                        'lon' => $loc['lon'],
                        'zone' => $loc['zone'],
                        'easting' => $loc['easting'],
                        'northing' => $loc['northing'],
                        'address' => $loc['address'],
                        'created_at' => $now,
                        'updated_at' => $now
                    ];
                }
            }

            GeolocatorField::insert($fieldArray);
            DB::table(self::SUPPORT_NAME)->insert($dataArray);
        }
    }

    /**
     * For a test record, add test data to field.
     *
     * @param  Field $field - The field to represent record data
     * @param  Record $record - Test record being created
     */
    public function createTestRecordField($field, $record) {
        $this->flid = $field->flid;
        $this->rid = $record->rid;
        $this->fid = $field->fid;
        $this->save();

        $this->addLocations(['[Desc]Matrix[Desc][LatLon]42.7314094,-84.476258[LatLon][UTM]16T:706605.1715423,4734077.6308044[UTM][Address]288 Farm Ln, East Lansing, MI 48823[Address]']);
    }

    /**
     * Validates the record data for a field against the field's options.
     *
     * @param  Field $field - The field to validate
     * @param  Request $request
     * @param  bool $forceReq - Do we want to force a required value even if the field itself is not required?
     * @return array - Array of errors
     */
    public function validateField($field, $request, $forceReq = false) {
        $req = $field->required;
        $value = $request->{$field->flid};

        if(($req==1 | $forceReq) && ($value==null | $value==""))
            return ['list'.$field->flid.'_chosen' => $field->name.' is required'];

        return array();
    }

    /**
     * Performs a rollback function on an individual field's record data.
     *
     * @param  Field $field - The field being rolled back
     * @param  Revision $revision - The revision being rolled back
     * @param  bool $exists - Field for record exists
     */
    public function rollbackField($field, Revision $revision, $exists=true) {
        if(!is_array($revision->oldData))
            $revision->oldData = json_decode($revision->oldData, true);

        if(!isset($revision->oldData[Field::_GEOLOCATOR][$field->flid]['data']))
            return null;

        // If the field doesn't exist or was explicitly deleted, we create a new one.
        if($revision->type == Revision::DELETE || !$exists) {
            $this->flid = $field->flid;
            $this->fid = $revision->fid;
            $this->rid = $revision->rid;
        }

        $this->save();
        $this->updateLocations($revision->oldData[Field::_GEOLOCATOR][$field->flid]['data']);
    }

    /**
     * Get the arrayed version of the field data to store in a record preset.
     *
     * @param  array $data - The data array representing the record preset
     * @param  bool $exists - Typed field exists and has data
     * @return array - The updated $data
     */
    public function getRecordPresetArray($data, $exists=true) {
        if($exists)
            $data['locations'] = self::locationsToOldFormat($this->locations()->get());
        else
            $data['locations'] = null;

        return $data;
    }

    /**
     * Get the required information for a revision data array.
     *
     * @param  Field $field - Optional field to get storage options for certain typed fields
     * @return mixed - The revision data
     */
    public function getRevisionData($field = null) {
        return self::locationsToOldFormat($this->locations()->get());
    }

    /**
     * Provides an example of the field's structure in an export to help with importing records.
     *
     * @param  string $slug - Field nickname
     * @param  string $expType - Type of export
     * @return mixed - The example
     *
     * [Desc]Matrix[Desc][LatLon]42.7314094,-84.476258[LatLon][UTM]16T:706605.1715423,4734077.6308044[UTM][Address]288 Farm Ln, East Lansing, MI 48823[Address]
     */
    public function getExportSample($slug,$type) {
        switch($type) {
            case "XML":
                $xml = '<' . Field::xmlTagClear($slug) . ' type="Geolocator">';
                $xml .= '<Location>';
                $xml .= '<Desc>' . utf8_encode('Matrix') . '</Desc>';
                $xml .= '<Lat>' . utf8_encode('42.7314094') . '</Lat>';
                $xml .= '<Lon>' . utf8_encode('-84.476258') . '</Lon>';
                $xml .= '<Zone>' . utf8_encode('16T') . '</Zone>';
                $xml .= '<East>' . utf8_encode('706605.1715423') . '</East>';
                $xml .= '<North>' . utf8_encode('4734077.6308044') . '</North>';
                $xml .= '<Address>' . utf8_encode('288 Farm Ln, East Lansing, MI 48823') . '</Address>';
                $xml .= '</Location>';
                $xml .= '</' . Field::xmlTagClear($slug) . '>';

                return $xml;
                break;
            case "JSON":
                $fieldArray = [$slug => ['type' => 'Geolocator']];

                $locArray = array();

                $locArray['desc'] = 'Matrix';
                $locArray['lat'] = '42.7314094';
                $locArray['lon'] = '-84.476258';
                $locArray['zone'] = '16T';
                $locArray['east'] = '706605.1715423';
                $locArray['north'] = '4734077.6308044';
                $locArray['address'] = '288 Farm Ln, East Lansing, MI 48823';
                $fieldArray[$slug]['value'] = $locArray;

                return $fieldArray;
                break;
        }

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
        $request->request->add([$flid.'_type' => $data->type]);
        if(isset($data->lat))
            $lat = $data->lat;
        else
            $lat = '';
        $request->request->add([$flid.'_lat' => $lat]);
        if(isset($data->lon))
            $lon = $data->lon;
        else
            $lon = '';
        $request->request->add([$flid.'_lon' => $lon]);
        if(isset($data->zone))
            $zone = $data->zone;
        else
            $zone = '';
        $request->request->add([$flid.'_zone' => $zone]);
        if(isset($data->east))
            $east = $data->east;
        else
            $east = '';
        $request->request->add([$flid.'_east' => $east]);
        if(isset($data->north))
            $north = $data->north;
        else
            $north = '';
        $request->request->add([$flid.'_north' => $north]);
        if(isset($data->address))
            $address = $data->address;
        else
            $address = '';
        $request->request->add([$flid.'_address' => $address]);
        $request->request->add([$flid.'_range' => $data->range]);

        return $request;
    }

    /**
     * Updates the request for an API to mimic record creation .
     *
     * @param  array $jsonField - JSON representation of field data
     * @param  int $flid - Field ID
     * @param  Request $recRequest
     * @param  int $uToken - Custom generated user token for file fields and tmp folders
     * @return Request - The update request
     */
    public function setRestfulRecordData($jsonField, $flid, $recRequest, $uToken=null) {
        $geo = array();
        foreach($jsonField->value as $loc) {
            $string = '[Desc]' . $loc['desc'] . '[Desc]';
            $string .= '[LatLon]' . $loc['lat'] . ',' . $loc['lon'] . '[LatLon]';
            $string .= '[UTM]' . $loc['zone'] . ':' . $loc['east'] . ',' . $loc['north'] . '[UTM]';
            $string .= '[Address]' . $loc['address'] . '[Address]';
            array_push($geo, $string);
        }
        $recRequest[$flid] = $geo;

        return $recRequest;
    }

    /**
     * Performs a keyword search on this field and returns any results.
     *
     * @param  int $flid - Field ID
     * @param  string $arg - The keywords
     * @return array - The RIDs that match search
     */
    public function keywordSearchTyped($flid, $arg) {
        return DB::table(self::SUPPORT_NAME)
            ->select("rid")
            ->where("flid", "=", $flid)
            ->where(function($query) use ($arg) {
                $query->where('desc','LIKE',"%$arg%")
                    ->orWhere('address','LIKE',"%$arg%");
            })
            ->distinct()
            ->pluck('rid')
            ->toArray();
    }

    /**
     * Performs an advanced search on this field and returns any results.
     *
     * @param  int $flid - Field ID
     * @param  array $query - The advance search user query
     * @return array - The RIDs that match search
     */
    public function advancedSearchTyped($flid, $query) {
        $range = $query[$flid.'_range'];
        $lat = $query[$flid."_lat"];
        $lon = $query[$flid."_lon"];

        $query = DB::table(self::SUPPORT_NAME);

        $distance = <<<SQL
(
  6371 * acos(cos(radians(?))
  * cos(radians(lat))
  * cos(radians(lon) - radians(?))
  + sin(radians(?))
  * sin( radians(lat)))
)
SQL;
        return $query->select(
            DB::raw("rid, {$distance} AS distance"))
            ->whereRaw("`flid` = ?")
            ->havingRaw("`distance` < ?")
            ->distinct()
            ->setBindings([$lat, $lon, $lat, $flid, $range])
            ->pluck('rid')
            ->toArray();
    }

    ///////////////////////////////////////////////END ABSTRACT FUNCTIONS///////////////////////////////////////////////

    /**
     * Gets the default values for a geolocator field.
     *
     * @param  Field $field - Field to pull defaults from
     * @return array - The defaults
     */
    public static function getLocationList($field) {
        $def = $field->default;
        return self::getListOptionsFromString($def);
    }

    /**
     * Overrides the delete function to first delete support fields.
     */
    public function delete() {
        $this->deleteLocations();
        parent::delete();
    }

    /**
     * Returns the locations for a record's generated list value.
     *
     * @return \Illuminate\Database\Query\Builder - Query of values
     */
    public function locations() {
        return DB::table(self::SUPPORT_NAME)->select("*")
            ->where("flid", "=", $this->flid)
            ->where("rid", "=", $this->rid);
    }

    /**
     * Determine if this field has data in the support table.
     *
     * @return bool - Has data
     */
    public function hasLocations() {
        return !! $this->locations()->count();
    }

    /**
     * Adds locations to the support table.
     *
     * @param  array $locations - Locations to add
     */
    public function addLocations(array $locations) {
        $now = date("Y-m-d H:i:s");

        foreach($locations as $location) {
            $desc = explode('[Desc]', $location)[1];
            $latlon = explode('[LatLon]', $location)[1];
            $utm = explode('[UTM]', $location)[1];
            $address = trim(explode('[Address]', $location)[1]);

            $lat = floatval(explode(',', $latlon)[0]);
            $lon = floatval(explode(',', $latlon)[1]);

            $utm_arr = explode(':', $utm);

            $zone = $utm_arr[0];
            $easting = explode(',', $utm_arr[1])[0];
            $northing = explode(',', $utm_arr[1])[1];

            DB::table('geolocator_support')->insert([
                'fid' => $this->fid,
                'rid' => $this->rid,
                'flid' => $this->flid,
                'desc' => $desc,
                'lat' => $lat,
                'lon' => $lon,
                'zone' => $zone,
                'easting' => $easting,
                'northing' => $northing,
                'address' => $address,
                'created_at' => $now,
                'updated_at' => $now
            ]);
        }
    }

    /**
     * Updates the current list of locations by deleting the old ones and adding the array that has both new and old.
     *
     * @param  array $locations - Locations to add
     */
    public function updateLocations(array $locations) {
        $this->deleteLocations();
        $this->addLocations($locations);
    }

    /**
     * Deletes locations from the support table.
     */
    public function deleteLocations() {
        DB::table(self::SUPPORT_NAME)
            ->where("flid", "=", $this->flid)
            ->where("rid", "=", $this->rid)
            ->delete();
    }

    /**
     * Turns the support table into the old format beforehand.
     *
     * @param  Collection $locations - Locations from support
     * @param  bool $array_string - Array of old format or string of old format
     * @return mixed - String or array of old format
     */
    public static function locationsToOldFormat($locations, $array_string = false) {
        $formatted = [];
        foreach($locations as $location) {
            $formatted[] = "[Desc]" . $location->desc . "[Desc][LatLon]"
                . $location->lat . "," . $location->lon . "[LatLon][UTM]"
                . $location->zone . ":" . $location->easting . "," . $location->northing . "[UTM][Address]"
                . $location->address . "[Address]";
        }

        if($array_string)
            return implode("[!]", $formatted);

        return $formatted;
    }

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
     * @return string - Geolocator formatted string of the converted coordinates
     */
    public static function geoConvert(Request $request) {
        if($request->type == 'latlon') {
            $lat = $request->lat;
            $lon = $request->lon;

            //to utm
            $con = new gPoint();
            $con->gPoint();
            $con->setLongLat($lon,$lat);
            $con->convertLLtoTM();
            $utm = $con->utmZone.':'.$con->utmEasting.','.$con->utmNorthing;

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

            $result = '[LatLon]'.$lat.','.$lon.'[LatLon][UTM]'.$utm.'[UTM][Address]'.$addr.'[Address]';

            return $result;
        } else if($request->type == 'utm') {
            $zone = $request->zone;
            $east = $request->east;
            $north = $request->north;

            //to latlon
            $con = new gPoint();
            $con->gPoint();
            $con->setUTM($east,$north,$zone);
            $con->convertTMtoLL();
            $lat = $con->lat;
            $lon = $con->long;

            //to address
            $con = app('geocoder');
            try {
                $res = $con->reverse($lat, $lon)->get()->first();
                if($res !== null) {
                    $addr = $res->getDisplayName();
                } else {
                    $addr = 'Address Not Found';
                }
            } catch(\Exception $e) {
                $addr = 'Address Not Found';
            }

            $result = '[LatLon]'.$lat.','.$lon.'[LatLon][UTM]'.$zone.':'.$east.','.$north.'[UTM][Address]'.$addr.'[Address]';

            return $result;
        } else if($request->type == 'geo') {
            $addr = $request->addr;

            //to latlon
            $con = app('geocoder');
            try {
                $res = $con->geocode($addr)->get()->first()->getCoordinates();
                $lat = $res->getLatitude();
                $lon = $res->getLongitude();
            } catch(\Exception $e) {
                $lat = 'null';
                $lon = 'null';
            }

            //to utm
            if($lat != 'null' && $lon != 'null') {
                $con = new gPoint();
                $con->gPoint();
                $con->setLongLat($lon,$lat);
                $con->convertLLtoTM();

                $utm = $con->utmZone.':'.$con->utmEasting.','.$con->utmNorthing;
            } else {
                $utm = 'null:null.null';
            }

            $result = '[LatLon]'.$lat.','.$lon.'[LatLon][UTM]'.$utm.'[UTM][Address]'.$addr.'[Address]';

            return $result;
        }
    }
}
