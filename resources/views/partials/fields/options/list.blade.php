@extends('fields.show')

@section('presetModal')
    @include('partials.fields.fieldValuePresetModals.addListPresetModal', ['presets' => $presets])
    @include('partials.fields.fieldValuePresetModals.createListPresetModal')
@stop

@section('fieldOptions')
    @include('partials.fields.options.config.list')

    @include('partials.fields.options.defaults.list')
@stop

@section('fieldOptionsJS')
    Kora.Fields.Options('List');
@stop
