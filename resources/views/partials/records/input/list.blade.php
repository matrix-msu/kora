<?php
    if($editRecord && $hasData)
        $listValue = $typedField->option;
    else if($editRecord)
        $listValue = null;
    else
        $listValue = $field->default;
?>
<div class="form-group mt-xxxl">
    <label>@if($field->required==1)<span class="oval-icon"></span> @endif{{$field->name}}</label>
    <span class="error-message"></span>
    {!! Form::select($field->flid,\App\ListField::getList($field,true), $listValue,
        ['class' => 'single-select preset-clear-chosen-js', 'id' => 'list'.$field->flid]) !!}
</div>