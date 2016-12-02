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
        }else if($type=='Combo List'){
            $type1 = $_REQUEST['cftype1'];
            $type2 = $_REQUEST['cftype2'];
            $name1 = '[Name]'.$_REQUEST['cfname1'].'[Name]';
            $name2 = '[Name]'.$_REQUEST['cfname2'].'[Name]';
            $options = "";

            $options = "[!Field1!][Type]";
            if($type1=='Text'){
                $options .= "Text[Type]".$name1."[Options][!Regex!][!Regex!][!MultiLine!]0[!MultiLine!]";
            }else if($type1=='Number'){
                $options .= "Number[Type]".$name1."[Options][!Max!]10[!Max!][!Min!]1[!Min!][!Increment!]1[!Increment!][!Unit!][!Unit!]";
            }else if($type1=='List'){
                $options .= "List[Type]".$name1."[Options][!Options!][!Options!]";
            }else if($type1=='Multi-Select List'){
                $options .= "Multi-Select List[Type]".$name1."[Options][!Options!][!Options!]";
            }else if($type1=='Generated List'){
                $options .= "Generated List[Type]".$name1."[Options][!Regex!][!Regex!][!Options!][!Options!]";
            }
            $options .= "[Options][!Field1!]";

            $options .= "[!Field2!][Type]";
            if($type2=='Text'){
                $options .= "Text[Type]".$name2."[Options][!Regex!][!Regex!][!MultiLine!]0[!MultiLine!]";
            }else if($type2=='Number'){
                $options .= "Number[Type]".$name2."[Options][!Max!]10[!Max!][!Min!]1[!Min!][!Increment!]1[!Increment!][!Unit!][!Unit!]";
            }else if($type2=='List'){
                $options .= "List[Type]".$name2."[Options][!Options!][!Options!]";
            }else if($type2=='Multi-Select List'){
                $options .= "Multi-Select List[Type]".$name2."[Options][!Options!][!Options!]";
            }else if($type2=='Generated List'){
                $options .= "Generated List[Type]".$name2."[Options][!Regex!][!Regex!][!Options!][!Options!]";
            }
            $options .= "[Options][!Field2!]";

            return $options;
        }else if($type=='Date'){
            return '[!Circa!]No[!Circa!][!Start!]1900[!Start!][!End!]2020[!End!][!Format!]MMDDYYYY[!Format!][!Era!]No[!Era!]';
        }else if($type=='Schedule'){
            return '[!Start!]1900[!Start!][!End!]2020[!End!][!Calendar!]No[!Calendar!]';
        }else if($type=="Geolocator"){
            return '[!Map!]No[!Map!][!DataView!]LatLon[!DataView!]';
        }else if($type=="Documents" | $type=="Playlist" | $type=="Video"){
            return '[!FieldSize!]0[!FieldSize!][!MaxFiles!]0[!MaxFiles!][!FileTypes!][!FileTypes!]';
        }else if($type=="Gallery"){
            return '[!FieldSize!]0[!FieldSize!][!ThumbSmall!]150x150[!ThumbSmall!][!ThumbLarge!]300x300[!ThumbLarge!][!MaxFiles!]0[!MaxFiles!][!FileTypes!][!FileTypes!]';
        }else if($type=="3D-Model"){
            return '[!FieldSize!]0[!FieldSize!][!MaxFiles!]1[!MaxFiles!][!FileTypes!][!FileTypes!]';
        }else if($type=="Associator"){
            return '[!SearchForms!][!SearchForms!]';
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