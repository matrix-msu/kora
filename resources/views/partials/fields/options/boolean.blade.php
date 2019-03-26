@extends('fields.show')

@section('presetModal')
    @include('partials.fields.fieldValuePresetModals.addRegexPresetModal', ['presets' => $presets])
    @include('partials.fields.fieldValuePresetModals.createRegexPresetModal')
@stop

@section('fieldOptions')
    <div class="form-group">
        {!! Form::label('default','Default') !!}
        <div class="check-box-half">
            <input type="checkbox" value="1" id="preset" class="check-box-input" name="default"
                    {{ ((!is_null($field['default']) && $field['default']) ? 'checked' : '') }}>
            <span class="check"></span>
            <span class="placeholder"></span>
        </div>
    </div>
@stop

@section('fieldOptionsJS')
    Kora.Fields.Options('Boolean');
@stop