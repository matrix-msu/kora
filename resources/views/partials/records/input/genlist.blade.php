<?php
    if($editRecord && $hasData) {
        $selected = explode('[!]',$typedField->options);
        $listOpts = array();
        foreach($selected as $op) {
            $listOpts[$op] = $op;
        }
    } else if($editRecord) {
        $selected = null;
        $listOpts = array();
    } else {
        $selected = explode('[!]',$field->default);
        $listOpts = \App\GeneratedListField::getList($field,false);
    }
?>
<div class="form-group mt-xxxl">
    <label>@if($field->required==1)<span class="oval-icon"></span> @endif{{$field->name}}</label>
    <span class="error-message"></span>
    {!! Form::select($field->flid.'[]',$listOpts, $selected, ['class' => 'multi-select modify-select preset-clear-chosen-js', 'multiple',
        'id' => 'list'.$field->flid]) !!}
</div>