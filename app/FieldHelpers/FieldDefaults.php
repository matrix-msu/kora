<?php
/**
 * Created by PhpStorm.
 * User: fritosxii
 * Date: 5/27/2015
 * Time: 10:40 AM
 */

namespace App\FieldHelpers;


class FieldDefaults {

    static function getOptions($type){
        if($type=="Text"){
            return '[!Regex!][!Regex!][!MultiLine!]0[!MultiLine!]';
        }
        else{
            return '';
        }
    }

    static function getDefault($type){
        if($type=="Text"){
            return '';
        }
        else{
            return '';
        }
    }
}