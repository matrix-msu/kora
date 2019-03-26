@php
    if($editRecord)
        $boolValue = $record->{$flid};
    else
        $boolValue = $field['default'];
@endphp
<div class="form-group mt-xxxl">
    <label>@if($field['required'])<span class="oval-icon"></span> @endif{{$field['name']}}</label>
    <span class="error-message"></span>
	
    <div class="check-box-half">
        <input type="checkbox" value="1" id="preset" class="check-box-input" name="{{$flid}}"
                {{ ((!is_null($boolValue) && $boolValue) ? 'checked' : '') }}>
        <span class="check"></span>
        <span class="placeholder"></span>
    </div>
</div>