@php
    if(isset($seq)) { //Combo List
        $seq = '_' . $seq;
        $title = $cfName;
        $default = null;
        $defClass = 'default-input-js';
    } else {
        $seq = '';
        $title = 'Default';
        $default = $field['default'];
        $defClass = '';
    }
@endphp
<div class="form-group mt-70-xl">
    {!! Form::label('default' . $seq, $title) !!}
    {!! Form::select('default' . $seq,[null=>'']+\App\KoraFields\ListField::getList($field), $default,
    ['class' => 'single-select list-default-js '.$defClass, 'data-placeholder' => 'Select the default value here (Value must be added above in order to select)']) !!}
</div>