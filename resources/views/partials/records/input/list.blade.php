@php
    if($editRecord)
        $listValue = $record->{$flid};
    else
        $listValue = $field['default'];
@endphp
<div class="form-group mt-xxxl">
    <label>@if($field['required'])<span class="oval-icon"></span> @endif{{$field['name']}}</label>
    <span class="error-message"></span>
    {!! Form::select($flid, [null=>'']+\App\KoraFields\ListField::getList($field), $listValue,
        ['class' => 'single-select preset-clear-chosen-js', 'id' => 'list'.$flid]) !!}
</div>
