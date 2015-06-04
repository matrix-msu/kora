<?php
/**
 * Created by PhpStorm.
 * User: fritosxii
 * Date: 6/4/2015
 * Time: 1:06 PM
 */

namespace App\FieldHelpers;


use App\Http\Controllers\FieldController;

class FieldValidation {

    static function validateField($field, $value){
        $field = FieldController::getField($field);
        if($field->type=='Text'){
            return FieldValidation::validateText($field, $value);
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
}