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
        }else if($type=='Number'){
            return '[!Max!][!Max!][!Min!][!Min!][!Increment!]1[!Increment!][!Unit!][!Unit!]';
        }else if($type=='List' or $type='Multi-Select List'){
            return '[!Options!][!Options!]';
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