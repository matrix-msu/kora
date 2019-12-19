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
<div class="form-group single-line-js">
    {!! Form::label('default' . $seq, $title) !!}
    <span class="error-message single-line"></span>
    {!! Form::text('default' . $seq, $default, ['class' => 'text-input text-default-js '.$defClass, 'placeholder' => 'Enter default value here']) !!}
</div>

<div class="form-group multi-line-js hidden">
    {!! Form::label('default' . $seq, $title) !!}
    <span class="error-message multi-line"></span>
    {!! Form::textarea('default' . $seq, $default, ['class' => 'text-area text-area-default text-area-default-js', 'placeholder' => "Enter default value here", 'disabled' => 'disabled']) !!}
</div>