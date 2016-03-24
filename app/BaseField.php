<?php namespace App;
/**
 * Created by PhpStorm.
 * User: ian.whalen
 * Date: 3/22/2016
 * Time: 11:12 AM
 */

use Illuminate\Database\Eloquent\Model;

abstract class BaseField extends Model
{
    protected $primaryKey = "id";

    public function record(){
        return $this->belongsTo('App\Record');
    }

    /**
     * Keyword search for a general field.
     *
     * @param array $args, Array of arguments for the search to use passed by reference.
     * @param bool $partial, True if partial values should be considered in the search.
     * @return bool, True if the field has satisfied the search parameters.
     */
    abstract public function keyword_search(array &$args, $partial);

}