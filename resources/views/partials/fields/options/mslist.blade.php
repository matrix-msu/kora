@extends('fields.show')

@section('presetModal')
    @include('partials.fields.fieldValuePresetModals.addListPresetModal', ['presets' => $presets])
    @include('partials.fields.fieldValuePresetModals.createListPresetModal')
@stop

@section('fieldOptions')
    @include('partials.fields.options.config.mslist')

    @include('partials.fields.options.defaults.mslist')
@stop

@section('fieldOptionsJS')
    Kora.Fields.Options('Multi-Select List');
@stop
