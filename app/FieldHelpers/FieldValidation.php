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
        } else if($field->type=='Date') {
            return FieldValidation::validateDate($field, $request);
        }else if($field->type=='Documents' | $field->type=='Gallery' | $field->type=='Playlist'){
            return FieldValidation::validateDocuments($field, $value);
        } else{
            return 'Field does not have a type';
        }
    }

    static function validateText($field, $value){
        $req = $field->required;
        $regex = FieldController::getFieldOption($field, 'Regex');

        if($req==1 && ($value==null | $value=="")){
            return $field->name.' field is required.';
        }

        if(($regex!=null | $regex!="") && !preg_match($regex,$value)){
            return 'Value for field '.$field->name.' does not match regex pattern.';
        }

        return '';
    }

    static function validateList($field, $value){
        $req = $field->required;
        $list = FieldController::getList($field);

        if($req==1 && ($value==null | $value=="")){
            return $field->name.' field is required.';
        }

        if($value!='' && !in_array($value,$list)){
            return "Value for field ".$field->name." not in list of options";
        }

        return '';
    }

    static function validateMultiSelectList($field, $value){
        $req = $field->required;
        $list = FieldController::getList($field);

        if($req==1 && ($value==null | $value=="")){
            return $field->name.' field is required.';
        }

        if(sizeof(array_diff($value,$list))>0){
            return "Value(s) for field ".$field->name." not in list of options";
        }

        return '';
    }

    static function validateGeneratedList($field, $value){
        $req = $field->required;
        $regex = FieldController::getFieldOption($field, 'Regex');

        if($req==1 && ($value==null | $value=="")){
            return $field->name.' field is required.';
        }

        foreach($value as $opt){
            if(($regex!=null | $regex!="") && !preg_match($regex,$opt)){
                return 'Value '.$opt.' for field '.$field->name.' does not match regex pattern.';
            }
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
            return $field->name.' field is required.';
        }

        if(($year<$start | $year>$end) && ($month!='' | $day!='')){
            return 'Year supplied for field '.$field->name.' is not in the range of '.$start.' and '.$end.'.';
        }

        if(!FieldController::validateDate($month,$day,$year)){
            return 'Invalid date for field '.$field->name.'. Either day given w/ no month provided, or day and month are impossible.';
        }

        return '';
    }

    static function validateDocuments($field, $value){
        $req = $field->required;

        if($req==1){
            if(glob(env('BASE_PATH').'storage/app/tmpFiles/'.$value.'/*.*') == false)
                return $field->name.' field is required. No files submitted.';
        }
    }

    static function validateDefault($field, $value){
        $req = $field->required;

        if($req==1 && ($value==null | $value=="")){
            return $field->name.' field is required.';
        }
    }
}