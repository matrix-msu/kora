@extends('fields.show')

@section('presetModal')
    @include('partials.fields.fieldValuePresetModals.addRegexPresetModal', ['presets' => $presets])
    @include('partials.fields.fieldValuePresetModals.createRegexPresetModal')
@stop

@section('fieldOptions')
    @include('partials.fields.options.defaults.boolean')
@stop

@section('fieldOptionsJS')
    Kora.Fields.Options('Boolean');
@stop
