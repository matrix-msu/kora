@php
    if(isset($seq)) { //Combo List
        $fieldLabel = '';
        $fieldDivID = 'default_'.$seq.'_'.$flid;
        $boolValue = null;
    } else if($editRecord) {
        $fieldLabel = $flid;
        $fieldDivID = $flid;
        $boolValue = $record->{$flid};
    } else {
        $fieldLabel = $flid;
        $fieldDivID = $flid;
        $boolValue = $field['default'];
    }
@endphp
<div class="form-group mt-xxxl">
    <label>@if(!isset($seq) && $field['required'])<span class="oval-icon"></span> @endif{{$field['name']}}</label>
    <span class="error-message"></span>

    <div class="check-box-half">
        <input type="hidden" name="{{$fieldLabel}}" value="0">
        <input type="checkbox" value="1" id="{{$fieldDivID}}" class="check-box-input" name="{{$fieldLabel}}"
                {{ ((!is_null($boolValue) && $boolValue) ? 'checked' : '') }}>
        <span class="check"></span>
        <span class="placeholder"></span>
    </div>
</div>
