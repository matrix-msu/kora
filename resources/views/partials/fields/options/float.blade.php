@extends('fields.show')

@section('fieldOptions')
    <div class="form-group">
        {!! Form::label('default','Default') !!}
        <span class="error-message"></span>
        <div class="number-input-container">
            <input
                type="number"
                name="default"
                class="text-input number-default-js"
                value="{{ $field['default'] }}"
                placeholder="Enter number here"
            >
        </div>
    </div>

    <div class="form-group mt-xl">
        {!! Form::label('min','Minimum Value') !!}
        <span class="error-message"></span>
        <div class="number-input-container">
            <input
                type="number"
                name="min"
                class="text-input number-min-js"
                id="min"
                value="{{ $field['options']['Min'] }}"
                placeholder="Enter minimum value here"
            >
        </div>
    </div>

    <div class="form-group mt-xl">
        {!! Form::label('max','Max Value') !!}
        <span class="error-message"></span>
        <div class="number-input-container">
            <input
                type="number"
                name="max"
                class="text-input number-max-js"
                id="max"
                value="{{ $field['options']['Max'] }}"
                placeholder="Enter max value here"
            >
        </div>
    </div>

    <div class="form-group mt-xl">
        {!! Form::label('unit','Unit of Measurement') !!}
        {!! Form::text('unit', $field['options']['Unit'], ['class' => 'text-input', 'placeholder' => 'Enter unit of measurement here']) !!}
    </div>
@stop

@section('fieldOptionsJS')
    Kora.Fields.Options('Number');
@stop
