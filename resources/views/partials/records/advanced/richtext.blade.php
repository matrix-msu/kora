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
    {!! Form::text($inputID, null, ['class' => 'text-input', 'placeholder' => 'Enter search text']) !!}
</div>
