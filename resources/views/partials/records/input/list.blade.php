<?php
    if($editRecord && $hasData)
        $listValue = $typedField->option;
    else if($editRecord)
        $listValue = null;
    else
        $listValue = $field->default;
?>
<div class="form-group mt-xxxl">
    <label>@if($field->required==1)<span class="oval-icon"></span> @endif{{$field->name}}: </label>
    {!! Form::select($field->flid,\App\ListField::getList($field,true), $listValue,
        ['class' => 'single-select', 'id' => 'list'.$field->flid]) !!}
</div>