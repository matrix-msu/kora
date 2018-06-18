<?php
    if($editRecord && $hasData)
        $listValues = explode('[!]',$typedField->options);
    else if($editRecord)
        $listValues = null;
    else
        $listValues = explode('[!]',$field->default);
?>
<div class="form-group mt-xxxl">
    <label>@if($field->required==1)<span class="oval-icon"></span> @endif{{$field->name}}: </label>
    <span class="error-message"></span>
    {!! Form::select($field->flid.'[]',\App\MultiSelectListField::getList($field,false), $listValues,
        ['class' => 'multi-select preset-clear-chosen-js', 'Multiple', 'id' => 'list'.$field->flid]) !!}
</div>