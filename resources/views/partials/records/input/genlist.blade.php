<?php
    if($editRecord) {
        $selected = explode('[!]',$record->{$flid});
        $listOpts = array();
        foreach($selected as $op) {
            $listOpts[$op] = $op;
        }
    } else {
        $selected = explode('[!]',$field['default']);
        $listOpts = App\KoraFields\GeneratedListField::getList($field);
    }
?>
<div class="form-group mt-xxxl">
    <label>@if($field['required'])<span class="oval-icon"></span> @endif{{$field['name']}}</label>
    <span class="error-message"></span>
    {!! Form::select($flid.'[]',$listOpts, $selected, ['class' => 'multi-select modify-select preset-clear-chosen-js', 'multiple',
        'id' => 'list'.$flid, 'data-placeholder' => 'Select Some Options or Type a New Option and Press Enter']) !!}
</div>
