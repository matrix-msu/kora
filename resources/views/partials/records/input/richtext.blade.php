<?php
    if($editRecord && $hasData)
        $textValue = $typedField->rawtext;
    else if($editRecord)
        $textValue = "";
    else
        $textValue = $field->default;
?>
<div class="form-group mt-xxxl">
    <label>@if($field->required==1)<span class="oval-icon"></span> @endif{{$field->name}}: </label>
    <textarea id="{{$field->flid}}" name="{{$field->flid}}" class="ckeditor-js">{{$textValue}}</textarea>
</div>