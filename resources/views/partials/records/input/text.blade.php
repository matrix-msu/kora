<?php
    if($editRecord && $hasData)
        $textValue = $typedField->text;
    else if($editRecord)
        $textValue = "";
    else
        $textValue = $field->default;
?>
<div class="form-group mt-xxxl">
    <label>@if($field->required==1)<span class="oval-icon"></span> @endif{{$field->name}}: </label>

    @if(\App\Http\Controllers\FieldController::getFieldOption($field,'MultiLine')==0)
        {!! Form::text($field->flid, $textValue, ['class' => 'text-input preset-clear-text-js']) !!}
    @endif
    @if(\App\Http\Controllers\FieldController::getFieldOption($field,'MultiLine')==1)
        {!! Form::textarea($field->flid, $textValue, ['class' => 'text-area preset-clear-text-js']) !!}
    @endif
</div>