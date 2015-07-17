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

    static function validateField($field, $value){
        $field = FieldController::getField($field);
        if($field->type=='Text'){
            return FieldValidation::validateText($field, $value);
        } else if($field->type='List') {
            return FieldValidation::validateList($field, $value);
        } else if($field->type=='Rich Text' | $field->type=='Number'){
            return FieldValidation::validateDefault($field, $value);
        }
        else{
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
            return "Value not in list of options";
        }

        return '';
    }

    static function validateDefault($field, $value){
        $req = $field->required;

        if($req==1 && ($value==null | $value=="")){
            return $field->name.' field is required.';
        }
    }
}