<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class DateField extends BaseField {

    protected $fillable = [
        'rid',
        'flid',
        'month',
        'day',
        'year',
        'era'
    ];

    public function keyword_search(array &$args, $partial)
    {
        // TODO: Implement keyword_search() method.
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
