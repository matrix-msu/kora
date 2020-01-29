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
    {!! Form::label($inputID, $fieldName) !!}
    <input class="text-input" type="number" name="{{$inputID}}_left" placeholder="Enter left bound (leave blank for -infinity)">
</div>
<div class="form-group mt-sm">
    <input class="text-input" type="number" name="{{$inputID}}_right" placeholder="Enter right bound (leave blank for infinity)">
</div>
<div class="form-group mt-sm">
    <div class="check-box-half">
        <input type="checkbox" value="1" id="active" class="check-box-input" name="{{$inputID}}_invert" />
        <span class="check"></span>
        <span class="placeholder">Searches outside the given range</span>
    </div>
</div>
