<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class DateField extends Model {

    protected $fillable = [
        'rid',
        'flid',
        'month',
        'day',
        'year',
        'era'
    ];

    protected $primaryKey = "id";

    public function record(){
        return $this->belongsTo('App\Record');
    }

    public static function validateDate($m,$d,$y){
        if($d!='' && !is_null($d)) {
            if ($m == '' | is_null($m)) {
                return false;
            } else {
                if($y=='')
                    $y=1;
                return checkdate($m, $d, $y);
            }
        }

        return true;
    }

}
