@extends('fields.show')

@section('fieldOptions')
    <div class="form-group">
        {!! Form::label('default','Default: ') !!}
        <input type="number" name="default" class="text-input" value="{{ $field->default }}">
    </div>

    <div class="form-group mt-xl">
        {!! Form::label('min','Minimum Value: ') !!}
        <input type="number" name="min" class="text-input" step="any" id="min"
            value="{{ \App\Http\Controllers\FieldController::getFieldOption($field, "Min") }}">
    </div>

    <div class="form-group mt-xl">
        {!! Form::label('max','Max Value: ') !!}
        <input type="number" name="max" class="text-input" step="any" id="max"
            value="{{ \App\Http\Controllers\FieldController::getFieldOption($field, "Max") }}">
    </div>

    <div class="form-group mt-xl">
        {!! Form::label('inc','Value Increment: ') !!}
        <input type="number" name="inc" class="text-input" step="any" id="inc"
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