<?php
    if($editRecord)
        $listValues = explode('[!]',$record->{$flid});
    else
        $listValues = explode('[!]',$field['default']);
?>
<div class="form-group mt-xxxl">
    <label>@if($field['required'])<span class="oval-icon"></span> @endif{{$field['name']}}</label>
    <span class="error-message"></span>
    {!! Form::select($field->flid.'[]',\App\MultiSelectListField::getList($field,false), $listValues,
        ['class' => 'multi-select preset-clear-chosen-js', 'Multiple', 'id' => 'list'.$field->flid]) !!}
</div>
