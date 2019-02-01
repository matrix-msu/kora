<?php
    if($editRecord)
        $listValue = $record->{$flid};
    else
        $listValue = $field['default'];

    $options = array();
    foreach ($field['options']['Options'] as $option) {
        $options['Options'][$option] = $option;
    }
?>
<div class="form-group mt-xxxl">
    <label>@if($field['required'])<span class="oval-icon"></span> @endif{{$field['name']}}</label>
    <span class="error-message"></span>
    {!! Form::select($flid, $options, $listValue,
        ['class' => 'single-select preset-clear-chosen-js', 'id' => 'list'.$flid]) !!}
</div>
