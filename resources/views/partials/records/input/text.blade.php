<?php
    if($editRecord && $hasData)
        $textValue = $typedField->text;
    else if($editRecord)
        $textValue = "";
    else
        $textValue = $field->default;
?>
<div class="form-group mt-xxxl">
    <label>@if($field->required==1)<span class="oval-icon"></span> @endif{{$field->name}}</label>
    <span class="error-message"></span>
	
    @if(\App\Http\Controllers\FieldController::getFieldOption($field,'MultiLine')==0)
        {!! Form::text($field->flid, $textValue, ['class' => 'text-input preset-clear-text-js', 'id' => $field->flid]) !!}
    @endif
    @if(\App\Http\Controllers\FieldController::getFieldOption($field,'MultiLine')==1)
        <?php
            $newLineCnt = substr_count($textValue, "\n");
            $taHeight = $newLineCnt*15;
            if($taHeight < 100)
                $taHeight = 100;
        ?>
        {!! Form::textarea($field->flid, $textValue, ['class' => 'text-area preset-clear-text-js', 'style' => 'height:'.$taHeight.'px', 'id' => $field->flid]) !!}
    @endif
</div>