@php
    if(isset($seq)) { //Combo List
        $fieldLabel = '';
        $fieldDivID = 'default_'.$seq.'_'.$flid;
        $textValue = null;
    } else if($editRecord) {
        $fieldLabel = $flid;
        $fieldDivID = $flid;
        $textValue = $record->{$flid};
    } else {
        $fieldLabel = $flid;
        $fieldDivID = $flid;
        $textValue = $field['default'];
    }
@endphp
<div class="form-group mt-xxxl">
    <label>@if(!isset($seq) && $field['required'])<span class="oval-icon"></span> @endif{{$field['name']}}</label>
    <span class="error-message"></span>
    <textarea id="{{$fieldDivID}}" name="{{$fieldLabel}}" class="ckeditor-js preset-clear-text-js">{{$textValue}}</textarea>
</div>
