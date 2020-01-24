@extends('fields.show')

@section('presetModal')
    @include('partials.fields.fieldValuePresetModals.addRegexPresetModal', ['presets' => $presets])
    @include('partials.fields.fieldValuePresetModals.createRegexPresetModal')
@stop

@section('fieldOptions')
    @include('partials.fields.options.defaults.text')

    @include('partials.fields.options.config.text')
@stop

@section('fieldOptionsJS')
    Kora.Fields.Options('Text');
@stop
