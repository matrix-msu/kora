@php
    if(isset($seq)) { //Combo List
         $fieldName = $cTitle;
         $inputID = $field[$seq]['flid']."_".$seq."_input[]";
         $listField = $field[$seq];
    } else {
         $fieldName = (array_key_exists('alt_name', $field) && $field['alt_name']!='') ? $field['name'].' ('.$field['alt_name'].')' : $field['name'];
         $inputID = $flid.'_input[]';
         $listField = $field;
    }
@endphp
<div class="form-group mt-xl">
    {!! Form::label($inputID, $fieldName) !!}
    {!! Form::select($inputID, App\KoraFields\MultiSelectListField::getList($listField), '', ["class" => "multi-select", "Multiple"]) !!}
</div>
