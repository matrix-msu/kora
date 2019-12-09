@php
    if($editRecord)
        $textValue = $record->{$flid};
    else
        $textValue = $field['default'];
@endphp
<div class="form-group mt-xxxl">
    <label>@if($field['required'])<span class="oval-icon"></span> @endif{{$field['name']}}</label>
    <span class="error-message"></span>
    <textarea id="{{$flid}}" name="{{$flid}}" class="ckeditor-js preset-clear-text-js">{{$textValue}}</textarea>
</div>
