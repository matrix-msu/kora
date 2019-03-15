@extends('fields.show')

@section('presetModal')
    @include('partials.fields.fieldValuePresetModals.addRegexPresetModal', ['presets' => $presets])
    @include('partials.fields.fieldValuePresetModals.createRegexPresetModal')
@stop

@section('fieldOptions')
    <div class="form-group single-line-js">
        {!! Form::label('default','Default') !!}
        <span class="error-message single-line"></span>
        {!! Form::text('default', $field['default'], ['class' => 'text-input text-default-js', 'placeholder' => 'Enter default value here']) !!}
    </div>

    <div class="form-group multi-line-js hidden">
        {!! Form::label('default','Default') !!}
        <span class="error-message multi-line"></span>
        {!! Form::textarea('default', $field['default'], ['class' => 'text-area text-area-default text-area-default-js', 'placeholder' => "Enter default value here", 'disabled' => 'disabled']) !!}
    </div>
    @include('partials.fields.options.defaults.text')
@stop

@section('fieldOptionsJS')
    Kora.Fields.Options('Text');
@stop
