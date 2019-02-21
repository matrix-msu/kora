<?php
    if($editRecord) {
        $selected = implode('[!]', json_decode($record->{$flid}));
    } else {
        $selected = implode('[!]',$field['default']);
    }
?>
<div class="form-group mt-xxxl">
    <label>@if($field['required'])<span class="oval-icon"></span> @endif{{$field['name']}}</label>
    <span class="error-message"></span>
    {!! Form::select($flid.'[]',App\KoraFields\GeneratedListField::getList($field), $selected, ['class' => 'multi-select modify-select preset-clear-chosen-js', 'multiple',
        'id' => 'list'.$flid, 'data-placeholder' => 'Select Some Options or Type a New Option and Press Enter']) !!}
</div>
