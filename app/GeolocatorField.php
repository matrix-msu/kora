<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

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
}
