@php
    if(isset($seq)) { //Combo List
         $fieldName = $cTitle;
         $inputID = $field[$seq]['flid']."_".$seq."_input";
    } else {
         $fieldName = (array_key_exists('alt_name', $field) && $field['alt_name']!='') ? $field['name'].' ('.$field['alt_name'].')' : $field['name'];
         $inputID = $flid.'_input';
    }
@endphp
<div class="form-group mt-xl">
    {!! Form::label($inputID, $fieldName) !!}
    <span class="error-message"></span>

    <div class="check-box-half">
        <input type="checkbox" value="1" id="preset" class="check-box-input" name="{{$inputID}}">
        <span class="check"></span>
        <span class="placeholder"></span>
    </div>
</div>