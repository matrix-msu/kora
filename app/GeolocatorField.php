<?php namespace App;

use App\FieldHelpers\gPoint;
use Geocoder\Geocoder;
use Geocoder\HttpAdapter\CurlHttpAdapter;
use Geocoder\Provider\NominatimProvider;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use PhpSpec\Exception\Exception;

class GeolocatorField extends BaseField {

    const SUPPORT_NAME = "geolocator_support";

    protected $fillable = [
        'rid',
        'flid',
        'locations'
    ];

    /**
     * Keyword search for a geolocator field.
     * We search only the address and description of any given location for the given parameters.
     *
     * @param array $args, the values to search for.
     * @param bool $partial, true if we should consider partial matches.
     * @return bool, true if an argument was found, false otherwise.
     */
    public function keywordSearch(array $args, $partial) {
        return false;
    }

    /**
     * Geolocator fields are always metafiable.
     *
     * @return bool
     */
    public function isMetafiable() {
        return true;
    }

    public function toMetadata(Field $field) {
        //
        // TODO: Implement me.
        //
        throw new Exception("Method not implemented...");
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
     *      [Desc]*[Desc][LatLon]*[LatLon][UTM]*[UTM][Address]*[Address] Format
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
}
