@php
    if(isset($seq)) { //Combo List
         $fieldName = $cTitle;
         $inputID = $field[$seq]['flid']."_".$seq;
    } else {
         $fieldName = (array_key_exists('alt_name', $field) && $field['alt_name']!='') ? $field['name'].' ('.$field['alt_name'].')' : $field['name'];
         $inputID = $flid;
    }
@endphp
<div class="form-group mt-xl">
    {!! Form::label($inputID.'_input', $fieldName) !!}
    {!! Form::text($inputID.'_input', null, ['class' => 'text-input', 'placeholder' => 'Enter search text']) !!}
</div>
<div class="form-group mt-sm">
    <div class="check-box-half">
        <input type="checkbox" value="1" id="active" class="check-box-input" name="{{$inputID}}_partial" />
        <span class="check"></span>
        <span class="placeholder">Partial</span>
        <span class="sub-text">(“Partial” Returns records where part of the data matches this search)</span>
    </div>
</div>
