<?php
/**
 * Created by PhpStorm.
 * User: fritosxii
 * Date: 6/4/2015
 * Time: 1:06 PM
 */

namespace App\FieldHelpers;


use App\Field;
use App\Http\Controllers\FieldController;

class FieldValidation {

    static function validateField($field, $value, $request){
        $field = FieldController::getField($field);
        if($field->type=='Text'){
            return FieldValidation::validateText($field, $value);
        } else if($field->type=='List') {
            return FieldValidation::validateList($field, $value);
        } else if($field->type=='Multi-Select List') {
            return FieldValidation::validateMultiSelectList($field, $value);
        } else if($field->type=='Rich Text' | $field->type=='Number' | $field->type=='Schedule'
            | $field->type=='Geolocator' | $field->type=='Associator' ){
            return FieldValidation::validateDefault($field, $value);
        } else if($field->type=='Generated List') {
            return FieldValidation::validateGeneratedList($field, $value);
        }  else if($field->type=='Combo List') {
            return FieldValidation::validateComboList($field, $request);
        } else if($field->type=='Date') {
            return FieldValidation::validateDate($field, $request);
        }else if($field->type=='Documents' | $field->type=='Gallery' | $field->type=='Playlist' | $field->type=="Video" | $field->type=="3D-Model"){
            return FieldValidation::validateDocuments($field, $value);
        } else{
            return trans('fieldhelpers_val.notype');
        }
    }

    static function validateText($field, $value){
        $req = $field->required;
        $regex = FieldController::getFieldOption($field, 'Regex');

        if($req==1 && ($value==null | $value=="")){
            return $field->name.trans('fieldhelpers_val.req');
        }

        if(($regex!=null | $regex!="") && !preg_match($regex,$value)){
            return trans('fieldhelpers_val.regex',['name'=>$field->name]);
        }

        return '';
    }

    static function validateList($field, $value){
        $req = $field->required;
        $list = FieldController::getList($field);

        if($req==1 && ($value==null | $value=="")){
            return $field->name.trans('fieldhelpers_val.req');
        }

        if($value!='' && !in_array($value,$list)){
            return trans('fieldhelpers_val.list',['name'=>$field->name]);
        }

        return '';
    }

    static function validateMultiSelectList($field, $value){
        $req = $field->required;
        $list = FieldController::getList($field);

        if($req==1 && ($value==null | $value=="")){
            return $field->name.trans('fieldhelpers_val.req');
        }

        if(sizeof(array_diff($value,$list))>0 && $value[0] !== ' '){
            return trans('fieldhelpers_val.mslist',['name'=>$field->name]);
        }

        return '';
    }

    static function validateGeneratedList($field, $value){
        $req = $field->required;
        $regex = FieldController::getFieldOption($field, 'Regex');

        if($req==1 && ($value==null | $value=="")){
            return $field->name.trans('fieldhelpers_val.req');
        }

        foreach($value as $opt){
            if(($regex!=null | $regex!="") && !preg_match($regex,$opt)){
                return trans('fieldhelpers_val.regexopt',['name'=>$field->name,'opt'=>$opt]);
            }
        }

        return '';
    }

    static function validateComboList($field, $request){
        $req = $field->required;
        $flid = $field->flid;

        if($req==1 && !isset($request[$flid.'_val'])){
            return $field->name.trans('fieldhelpers_val.req');
        }

        return '';
    }

    static function validateDate($field, $request){
        $req = $field->required;
        $start = FieldController::getFieldOption($field,'Start');
        $end = FieldController::getFieldOption($field,'End');
        $month = $request->input('month_'.$field->flid,'');
        $day = $request->input('day_'.$field->flid,'');
        $year = $request->input('year_'.$field->flid,'');

        if($req==1 && $month=='' && $day=='' && $year==''){
            return $field->name.trans('fieldhelpers_val.req');
        }

        if(($year<$start | $year>$end) && ($month!='' | $day!='')){
            return trans('fieldhelpers_val.year',['name'=>$field->name,'start'=>$start,'end'=>$end]);
        }

        if(!FieldController::validateDate($month,$day,$year)){
            return trans('fieldhelpers_val.date',['name'=>$field->name]);
        }

        return '';
    }

    static function validateDocuments($field, $value){
        $req = $field->required;

        if($req==1){
            if(glob(env('BASE_PATH').'storage/app/tmpFiles/'.$value.'/*.*') == false)
                return $field->name.trans('fieldhelpers_val.file');
        }
    }

    static function validateDefault($field, $value){
        $req = $field->required;

        if($req==1 && ($value==null | $value=="")){
            return $field->name.trans('fieldhelpers_val.req');
        }
    }
}