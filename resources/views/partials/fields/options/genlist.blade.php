@extends('fields.show')

@section('presetModal')
    @include('partials.fields.fieldValuePresetModals.addListPresetModal', ['presets' => $presets])
    @include('partials.fields.fieldValuePresetModals.createListPresetModal')
    @include('partials.fields.fieldValuePresetModals.addRegexPresetModal', ['presets' => $presets])
    @include('partials.fields.fieldValuePresetModals.createRegexPresetModal')
@stop

@section('fieldOptions')
    @include('partials.fields.options.defaults.genlist')

    @include('partials.fields.options.config.genlist')
@stop

@section('fieldOptionsJS')
    Kora.Fields.Options('Generated List');
@stop
