<?php
/**
 * Created by PhpStorm.
 * User: fritosxii
 * Date: 5/27/2015
 * Time: 10:40 AM
 */

namespace app\FieldHelpers;


class FieldDefaults {

    static function getOptions($type){
        if($type=="Text"){
            return '[!Regex!][!Regex!][!MultiLine!]no[!MultiLine!]';
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