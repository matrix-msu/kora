@extends('fields.show')

@section('fieldOptions')
    <div class="form-group">
        {!! Form::label('default','Default: ') !!}
        <span class="error-message"></span>
        <input type="number" name="default" class="text-input number-default-js" value="{{ $field->default }}" placeholder="Enter number here">
    </div>

    <div class="form-group mt-xl">
        {!! Form::label('min','Minimum Value: ') !!}
        <span class="error-message"></span>
        <input type="number" name="min" class="text-input number-min-js" step="any" id="min"
            value="{{ \App\Http\Controllers\FieldController::getFieldOption($field, "Min") }}">
    </div>

    <div class="form-group mt-xl">
        {!! Form::label('max','Max Value: ') !!}
        <span class="error-message"></span>
        <input type="number" name="max" class="text-input number-max-js" step="any" id="max"
            value="{{ \App\Http\Controllers\FieldController::getFieldOption($field, "Max") }}">
    </div>

    <div class="form-group mt-xl">
        {!! Form::label('inc','Value Increment: ') !!}
        <span class="error-message"></span>
        <input type="number" name="inc" class="text-input number-step-js" step="any" id="inc"
            value="{{ \App\Http\Controllers\FieldController::getFieldOption($field, "Increment") }}">
    </div>

    <div class="form-group mt-xl">
        {!! Form::label('unit','Unit of Measurement: ') !!}
        {!! Form::text('unit', \App\Http\Controllers\FieldController::getFieldOption($field,'Unit'), ['class' => 'text-input']) !!}
    </div>
@stop

@section('fieldOptionsJS')
    Kora.Fields.Options('Number');
@stop