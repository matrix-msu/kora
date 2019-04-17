@extends('fields.show')

@section('presetModal')
    @include('partials.fields.fieldValuePresetModals.addListPresetModal', ['presets' => $presets])
    @include('partials.fields.fieldValuePresetModals.createListPresetModal')
@stop

@section('fieldOptions')
    @include('partials.fields.options.defaults.list')
    <div class="form-group mt-70-xl">
        {!! Form::label('default','Default') !!}
        {!! Form::select('default',[null=>'']+\App\KoraFields\ListField::getList($field), $field['default'],
        ['class' => 'single-select list-default-js', 'data-placeholder' => 'Select the default value here (Value must be added above in order to select)']) !!}
    </div>
@stop

@section('fieldOptionsJS')
    Kora.Fields.Options('List');
@stop
