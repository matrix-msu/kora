@extends('fields.show')

@section('presetModal')
    @include('partials.fields.fieldValuePresetModals.addListPresetModal', ['presets' => $presets])
    @include('partials.fields.fieldValuePresetModals.createListPresetModal')
@stop

@section('fieldOptions')
    @include('partials.fields.options.config.mslist')
    <div class="form-group mt-xxxl">
        {!! Form::label('default','Default') !!}
        {!! Form::select('default[]', App\KoraFields\MultiSelectListField::getList($field), $field['default'],
        ['class' => 'multi-select list-default-js', 'multiple', 'data-placeholder' => 'Select the default values here (Values must be added above in order to select)']) !!}
    </div>
@stop

@section('fieldOptionsJS')
    Kora.Fields.Options('Multi-Select List');
@stop
