<?php namespace App;

use App\FieldHelpers\gPoint;
use App\Http\Controllers\FieldController;
use App\Http\Controllers\RevisionController;
use Carbon\Carbon;
use Geocoder\Geocoder;
use Geocoder\HttpAdapter\CurlHttpAdapter;
use Geocoder\Provider\NominatimProvider;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use PhpSpec\Exception\Exception;

class GeolocatorField extends BaseField {

    const SUPPORT_NAME = "geolocator_support";
    const FIELD_OPTIONS_VIEW = "fields.options.geolocator";
    const FIELD_ADV_OPTIONS_VIEW = "partials.field_option_forms.geolocator";

    protected $fillable = [
        'rid',
        'flid',
        'locations'
    ];

    public static function getOptions(){
        return '[!Map!]No[!Map!][!DataView!]LatLon[!DataView!]';
    }

    public static function getExportSample($field,$type){
        switch ($type){
            case "XML":
                $xml = '<' . Field::xmlTagClear($field->slug) . ' type="' . $field->type . '">';
                $xml .= '<Location>';
                $xml .= '<Desc>' . utf8_encode('LOCATION DESCRIPTION') . '</Desc>';
                $xml .= '<Lat>' . utf8_encode('i.e. 13') . '</Lat>';
                $xml .= '<Lon>' . utf8_encode('i.e. 14.5') . '</Lon>';
                $xml .= '<Zone>' . utf8_encode('i.e. 38T') . '</Zone>';
                $xml .= '<East>' . utf8_encode('i.e. 59233.235234') . '</East>';
                $xml .= '<North>' . utf8_encode('i.e. 52833.265454') . '</North>';
                $xml .= '<Address>' . utf8_encode('TEXTUAL REPRESENTATION OF LOCATION') . '</Address>';
                $xml .= '</Location>';
                $xml .= '</' . Field::xmlTagClear($field->slug) . '>';

                return $xml;
                break;
            case "JSON":
                $fieldArray = array('name' => $field->slug, 'type' => $field->type);
                $fieldArray['locations'] = array();
                $locArray = array();

                $locArray['desc'] = 'LOCATION DESCRIPTION';
                $locArray['lat'] = 'i.e. 13';
                $locArray['lon'] = 'i.e. 14.5';
                $locArray['zone'] = 'i.e. 38T';
                $locArray['east'] = 'i.e. 59233.235234';
                $locArray['north'] = 'i.e. 52833.265454';
                $locArray['address'] = 'TEXTUAL REPRESENTATION OF LOCATION';
                array_push($fieldArray['locations'], $locArray);

                return $fieldArray;
                break;
        }

    }

    public static function updateOptions($pid, $fid, $flid, $request){
        $reqDefs = $request->default;
        $default = $reqDefs[0];
        for($i=1;$i<sizeof($reqDefs);$i++){
            $default .= '[!]'.$reqDefs[$i];
        }

        FieldController::updateRequired($pid, $fid, $flid, $request->required);
        FieldController::updateSearchable($pid, $fid, $flid, $request);
        FieldController::updateDefault($pid, $fid, $flid, $default);
        FieldController::updateOptions($pid, $fid, $flid, 'Map', $request->map);
        FieldController::updateOptions($pid, $fid, $flid, 'DataView', $request->view);
    }

    public static function createNewRecordField($field, $record, $value){
        $gf = new self();
        $gf->flid = $field->flid;
        $gf->rid = $record->rid;
        $gf->fid = $field->fid;
        $gf->save();

        $gf->addLocations($value);
    }

    public static function editRecordField($field, $record, $value){
        //we need to check if the field exist first
        $gf = self::where('rid', '=', $record->rid)->where('flid', '=', $field->flid)->first();
        if(!is_null($gf) && !is_null($value)){
            $gf->updateLocations($value);
        }
        elseif(!is_null($gf) && is_null($value)){
            $gf->delete();
            $gf->deleteLocations();
        }
        else {
            self::createNewRecordField($field, $record, $value);
        }
    }

    public static function massAssignRecordField($flid, $record, $form_field_value, $overwrite){
        $matching_record_fields = $record->geolocatorfields()->where("flid", '=', $flid)->get();
        $record->updated_at = Carbon::now();
        $record->save();
        if ($matching_record_fields->count() > 0) {
            $geolocatorfield = $matching_record_fields->first();
            if ($overwrite == true || ! $geolocatorfield->hasLocations()) {
                $revision = RevisionController::storeRevision($record->rid, 'edit');
                $geolocatorfield->updateLocations($form_field_value);
                $revision->oldData = RevisionController::buildDataArray($record);
                $revision->save();
            }
        } else {
            $gf = new self();
            $revision = RevisionController::storeRevision($record->rid, 'edit');
            $gf->flid = $flid;
            $gf->rid = $record->rid;
            $gf->save();

            $gf->addLocations($form_field_value);

            $revision->oldData = RevisionController::buildDataArray($record);
            $revision->save();
        }
    }

    public static function createTestRecordField($field, $record){
        $gf = new self();
        $gf->flid = $field->flid;
        $gf->rid = $record->rid;
        $gf->fid = $field->fid;
        $gf->save();

        $gf->addLocations(['[Desc]K3TR[Desc][LatLon]13,37[LatLon][UTM]37P:283077.41182513,1437987.6443346[UTM][Address] Appelstra�e Hanover Lower Saxony[Address]']);
    }

    public static function setRestfulAdvSearch($data, $field, $request){
        $request->request->add([$field->flid.'_type' => $data->type]);
        if(isset($data->lat))
            $lat = $data->lat;
        else
            $lat = '';
        $request->request->add([$field->flid.'_lat' => $lat]);
        if(isset($data->lon))
            $lon = $data->lon;
        else
            $lon = '';
        $request->request->add([$field->flid.'_lon' => $lon]);
        if(isset($data->zone))
            $zone = $data->zone;
        else
            $zone = '';
        $request->request->add([$field->flid.'_zone' => $zone]);
        if(isset($data->east))
            $east = $data->east;
        else
            $east = '';
        $request->request->add([$field->flid.'_east' => $east]);
        if(isset($data->north))
            $north = $data->north;
        else
            $north = '';
        $request->request->add([$field->flid.'_north' => $north]);
        if(isset($data->address))
            $address = $data->address;
        else
            $address = '';
        $request->request->add([$field->flid.'_address' => $address]);
        $request->request->add([$field->flid.'_range' => $data->range]);

        return $request;
    }

    public static function setRestfulRecordData($field, $flid, $recRequest){
        $geo = array();
        foreach($field->locations as $loc) {
            $string = '[Desc]' . $loc['desc'] . '[Desc]';
            $string .= '[LatLon]' . $loc['lat'] . ',' . $loc['lon'] . '[LatLon]';
            $string .= '[UTM]' . $loc['zone'] . ':' . $loc['east'] . ',' . $loc['north'] . '[UTM]';
            $string .= '[Address]' . $loc['address'] . '[Address]';
            array_push($geo, $string);
        }
        $recRequest[$flid] = $geo;

        return $recRequest;
    }

    public static function getRecordPresetArray($field, $record, $data, $flid_array){
        $geofield = GeolocatorField::where('rid', '=', $record->rid)->where('flid', '=', $field->flid)->first();

        if ($geofield->hasLocations()) {
            $data['locations'] = GeolocatorField::locationsToOldFormat($geofield->locations()->get());
        }
        else {
            $data['locations'] = null;
        }

        $flid_array[] = $field->flid;

        return array($data,$flid_array);
    }

    /**
     * Gets the default locations from the field options.
     *
     * @param $field
     * @return array
     */
    public static function getLocationList($field)
    {
        $def = $field->default;
        $options = array();
        if ($def == '') {
            //skip
        } else if (!strstr($def, '[!]')) {
            $options = [$def => $def];
        } else {
            $opts = explode('[!]', $def);
            foreach ($opts as $opt) {
                $options[$opt] = $opt;
            }
        }
        return $options;
    }

    /**
     * The query for locations in a geolocator field.
     * Use ->get() to obtain all locations.
     * @return Builder
     */
    public function locations() {
        return DB::table(self::SUPPORT_NAME)->select("*")
            ->where("flid", "=", $this->flid)
            ->where("rid", "=", $this->rid);
    }

    /**
     * True if there are locations associated with a particular Geolocator field.
     *
     * @return bool
     */
    public function hasLocations() {
        return !! $this->locations()->count();
    }


    /**
     * Puts an array of events into the old format.
     *      - "Old Format" meaning, an array of the locations formatted as
     *        [Desc]<Description>[Desc][LatLon]<Latitude,Longitude>[LatLon][UTM]<Zone:Easting,Northing>[UTM][Address]<Address>[Address]
     *
     * @param array $locations, array of StdObjects representing locations.
     * @param bool $array_string, should this be in the old *[!]*[!]...[!]* format?
     * @return array | string
     */
    public static function locationsToOldFormat(array $locations, $array_string = false) {
        $formatted = [];
        foreach ($locations as $location) {
            $formatted[] = "[Desc]" . $location->desc . "[Desc][LatLon]"
                . $location->lat . "," . $location->lon . "[LatLon][UTM]"
                . $location->zone . ":" . $location->easting . "," . $location->northing . "[UTM][Address]"
                . $location->address . "[Address]";
        }

        if ($array_string) {
            return implode("[!]", $formatted);
        }

        return $formatted;
    }

    /**
     * Adds locations to the geolocator support table.
     *
     * @param array $locations, array of locations as they are given from the create/edit form javascript.
     *      Format: [Desc]<Description>[Desc][LatLon]<Latitude,Longitude>[LatLon][UTM]<Zone:Easting,Northing>[UTM][Address]<Address>[Address]
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
     * Updates locations associated with this field.
     *
     * @param array $locations
     */
    public function updateLocations(array $locations) {
        $this->deleteLocations();
        $this->addLocations($locations);
    }

    /**
     * Deletes locations associated with this geolocator field.
     */
    public function deleteLocations() {
        DB::table(self::SUPPORT_NAME)
            ->where("flid", "=", $this->flid)
            ->where("rid", "=", $this->rid)
            ->delete();
    }

    /**
     * @param null $field
     * @return array
     */
    public function getRevisionData($field = null) {
        return self::locationsToOldFormat($this->locations()->get());
    }

    /**
     * Rollback a geolocator field based on a revision.
     *
     * @param Revision $revision
     * @param Field $field
     * @return GeolocatorField
     */
    public static function rollback(Revision $revision, Field $field) {
        if (!is_array($revision->data)) {
            $revision->data = json_decode($revision->data, true);
        }

        if (is_null($revision->data[Field::_GEOLOCATOR][$field->flid]['data'])) {
            return null;
        }

        $geofield = self::where("flid", "=", $field->flid)->where("rid", "=", $revision->rid)->first();

        // If the field doesn't exist or was explicitly deleted, we create a new one.
        if ($revision->type == Revision::DELETE || is_null($geofield)) {
            $geofield = new self();
            $geofield->flid = $field->flid;
            $geofield->fid = $revision->fid;
            $geofield->rid = $revision->rid;
        }

        $geofield->save();
        $geofield->updateLocations($revision->data[Field::_GEOLOCATOR][$field->flid]['data']);

        return $geofield;
    }

    /**
     * Build an advanced search query for a geolocator field.
     *
     * @param $flid, field id.
     * @param $query, query array.
     * @return Builder
     */
    public static function getAdvancedSearchQuery($flid, $query) {
        $range = $query[$flid.'_range'];

        // Depending on the search type, we must convert the input to latitude and longitude.
        switch($query[$flid.'_type']) {
            case "LatLon":
                $lat = $query[$flid."_lat"];
                $lon = $query[$flid."_lon"];
                break;
            case "UTM":
                $point = self::UTMToPoint($query[$flid."_zone"],
                                           $query[$flid."_east"],
                                           $query[$flid."_north"]);
                $lat = $point->Lat();
                $lon = $point->Long();
                break;
            case "Address":
                $point = self::addressToPoint($query[$flid."_address"]);
                $lat = $point->Lat();
                $lon = $point->Long();
                break;
        }

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
            ->setBindings([$lat, $lon, $lat, $flid, $range]);
    }

    /**
     * Convert UTM to gPoint instance.
     *
     * @param string $zone, valid UTM zone.
     * @param float $easting, easting UTM value (meters east).
     * @param float $northing, northing UTM value (meters north).
     * @return gPoint, point with converted latitude and longitude values in member variables.
     *                 Use ->Lat() and ->Long() to obtain converted values.
     */
    public static function UTMToPoint($zone, $easting, $northing) {
        $point = new gPoint();
        $point->gPoint();
        $point->setUTM($easting, $northing, $zone);
        $point->convertTMtoLL();
        return $point;
    }

    /**
     * Convert address to gPoint instance.
     *
     * @param string $address
     * @return gPoint, point with converted latitude and longitude values in member variables.
     *                 Use ->Lat() and ->Long() to obtain converted values.
     */
    public static function addressToPoint($address) {
        $coder = new Geocoder();
        $coder->registerProviders([
            new NominatimProvider(
                new CurlHttpAdapter(),
                'http://nominatim.openstreetmap.org/',
                'en'
            )
        ]);

        $result = $coder->geocode($address);
        $point = new gPoint();
        $point->gPoint();
        $point->setLongLat($result->getLongitude(), $result->getLatitude());

        return $point;
    }

    /**
     * Delete the geolocator field.
     * @throws \Exception
     */
    public function delete() {
        $this->deleteLocations();
        parent::delete();
    }

    public static function validate($field, $value){
        $req = $field->required;

        if($req==1 && ($value==null | $value=="")){
            return $field->name.trans('fieldhelpers_val.req');
        }
    }

    /**
     * Validates the address for a Geolocator field.
     *
     * @param  Request $request
     * @return bool - Result of address validity
     */
    public static function validateAddress(Request $request) {
        $address = $request['address'];

        $coder = new Geocoder();
        $coder->registerProviders([
            new NominatimProvider(
                new CurlHttpAdapter(),
                'http://nominatim.openstreetmap.org/',
                'en'
            )
        ]);

        try {
            $coder->geocode($address);
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
            $con = new \Geocoder\Geocoder();
            $con->registerProviders([
                new NominatimProvider(
                    new CurlHttpAdapter(), 'http://nominatim.openstreetmap.org/', 'en'
                )
            ]);
            try {
                $res = $con->geocode($lat.', '.$lon);
                $addr = $res->getStreetNumber().' '.$res->getStreetName().' '.$res->getCity().' '.$res->getRegion();
            } catch(\Exception $e) {
                $addr = 'null';
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
            $con = new \Geocoder\Geocoder();
            $con->registerProviders([
                new NominatimProvider(
                    new CurlHttpAdapter(), 'http://nominatim.openstreetmap.org/', 'en'
                )
            ]);
            try {
                $res = $con->geocode($lat.', '.$lon);
                $addr = $res->getStreetNumber().' '.$res->getStreetName().' '.$res->getCity().' '.$res->getRegion();
            } catch(\Exception $e) {
                $addr = 'null';
            }

            $result = '[LatLon]'.$lat.','.$lon.'[LatLon][UTM]'.$zone.':'.$east.','.$north.'[UTM][Address]'.$addr.'[Address]';

            return $result;
        } else if($request->type == 'geo') {
            $addr = $request->addr;

            //to latlon
            $con = new \Geocoder\Geocoder();
            $con->registerProviders([
                new NominatimProvider(
                    new CurlHttpAdapter(), 'http://nominatim.openstreetmap.org/', 'en'
                )
            ]);
            try {
                $res = $con->geocode($addr);
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
