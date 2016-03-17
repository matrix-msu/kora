<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class GeolocatorField extends Model {

    protected $fillable = [
        'rid',
        'flid',
        'locations'
    ];

    protected $primaryKey = "id";

    public function record(){
        return $this->belongsTo('App\Record');
    }

    public static function getLocationList($field)
    {
        $def = $field->default;
        $options = array();

        if ($def == '') {
            //skip
        } else if (!strstr($def, '[!]')) {
            $options = [$def => 'Description: '.explode('[Desc]',$def)[1].' | LatLon: '.explode('[LatLon]',$def)[1].' | UTM: '.explode('[UTM]',$def)[1].' | Address: '.explode('[Address]',$def)[1]];
        } else {
            $opts = explode('[!]', $def);
            foreach ($opts as $opt) {
                $options[$opt] = 'Description: '.explode('[Desc]',$opt)[1].' | LatLon: '.explode('[LatLon]',$opt)[1].' | UTM: '.explode('[UTM]',$opt)[1].' | Address: '.explode('[Address]',$opt)[1];
            }
        }

        return $options;
    }
}
