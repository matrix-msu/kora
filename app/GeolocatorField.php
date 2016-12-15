<?php namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GeolocatorField extends BaseField {

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
        $locations = $this->getLocations();

        foreach($locations as $location) {
            if(self::keywordRoutine($args, $partial, $location['Address'])) return true; // Found arguement in address.
            if(self::keywordRoutine($args, $partial, $location['Desc'])) return true; // Found argument in description.
        }

        return false;
    }

    /**
     * Get the locations of the geolocator field, including the default value.
     *
     * @return array, list of locations indexed with the descriptor.
     */
    public function getLocations() {
        $field = Field::where("flid", "=", $this->flid)->first();
        $defaultStr = $field->default;

        $locations = [];
        if ($defaultStr != '') {
            foreach(explode('[!]', $defaultStr) as $location) {
                $locArray = [];
                $locArray['Desc'] = explode( '[Desc]', $location)[1];
                $locArray['LatLon'] = explode('[LatLon]', $location)[1];
                $locArray['UTM'] = explode('[UTM]', $location)[1];
                $locArray['Address'] = explode('[Address]', $location)[1];

                $locations[] = $locArray;
            }
        }

        foreach(explode('[!]', $this->locations) as $location) {
            $locArray = [];
            $locArray['Desc'] = explode( '[Desc]', $location)[1];
            $locArray['LatLon'] = explode('[LatLon]', $location)[1];
            $locArray['UTM'] = explode('[UTM]', $location)[1];
            $locArray['Address'] = explode('[Address]', $location)[1];

            $locations[] = $locArray;
        }

        return $locations;
    }

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
     * Geolocator fields are always metafiable.
     *
     * @return bool
     */
    public function isMetafiable() {
        return true;
    }

    public function toMetadata(Field $field) {
        $locations = explode("[!]", $this->locations);

        $locations_and_info = [];
        foreach ($locations as $location) {
            $info_collection = new Collection();

            $info_collection->put("Desc", explode("[Desc]", $location)[1]);
            $info_collection->put("LatLon", explode("[LatLon]", $location)[1]);
            $info_collection->put("UTM", explode("[UTM]", $location)[1]);
            $info_collection->put("Address", explode("[Address]", $location)[1]);

            $locations_and_info[] = $info_collection;
        }

        return $locations_and_info;
    }

    /**
     * The query for locations in a geolocator field.
     * Use ->get() to obtain all locations.
     * @return Builder
     */
    public function locations() {
        return DB::table("geolocator_support")->select("*")
            ->where("flid", "=", $this->flid)
            ->where("rid", "=", $this->rid);
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
}
