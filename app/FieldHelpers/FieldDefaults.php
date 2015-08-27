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
        }else if($type=='List' or $type=='Multi-Select List'){
            return '[!Options!][!Options!]';
        }else if($type=='Generated List'){
            return '[!Regex!][!Regex!][!Options!][!Options!]';
        }else if($type=='Date'){
            return '[!Circa!]No[!Circa!][!Start!]1900[!Start!][!End!]2020[!End!][!Format!]MMDDYYYY[!Format!][!Era!]Off[!Era!]';
        }else if($type=='Schedule'){
            return '[!Start!]1900[!Start!][!End!]2020[!End!][!Calendar!]No[!Calendar!]';
        }else if($type="Geolocator"){
            return '[!Map!]No[!Map!][!DataView!]LatLon[!DataView!]';
        }else if($type="Associator"){
            return '[!Forms!][!Forms!][!SearchForms!][!SearchForms!]';
        }
        else{
            return '';
        }
    }

    static function getDefault($type){
        if($type=="Date"){
            return '[M][M][D][D][Y][Y]';
        }
        else{
            return '';
        }
    }
}