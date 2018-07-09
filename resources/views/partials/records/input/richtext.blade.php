<?php
    if($editRecord && $hasData)
        $textValue = $typedField->rawtext;
    else if($editRecord)
        $textValue = "";
    else
        $textValue = $field->default;
?>
<div class="form-group mt-xxxl">
    <label>@if($field->required==1)<span class="oval-icon"></span> @endif{{$field->name}}</label>
    <span class="error-message"></span>
    <textarea id="{{$field->flid}}" name="{{$field->flid}}" class="ckeditor-js preset-clear-text-js">{{$textValue}}</textarea>
</div>