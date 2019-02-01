<?php
    if($editRecord)
        $listValue = $record->{$flid};
    else
        $listValue = $field['default'];
?>
<div class="form-group mt-xxxl">
    <label>@if($field['required'])<span class="oval-icon"></span> @endif{{$field['name']}}</label>
    <span class="error-message"></span>
    {!! Form::select($flid,\App\ListField::getList($field,true), $listValue,
        ['class' => 'single-select preset-clear-chosen-js', 'id' => 'list'.$flid]) !!}
</div>
