<?php
    if($editRecord)
        $listValues = implode('[!]',$record->{$flid});
    else
        $listValues = implode('[!]',$field['default']);
?>
<div class="form-group mt-xxxl">
    <label>@if($field['required'])<span class="oval-icon"></span> @endif{{$field['name']}}</label>
    <span class="error-message"></span>
    {!! Form::select($flid.'[]', App\KoraFields\MultiSelectListField::getList($field), $listValues,
        ['class' => 'multi-select preset-clear-chosen-js', 'Multiple', 'id' => 'list'.$flid]) !!}
</div>
