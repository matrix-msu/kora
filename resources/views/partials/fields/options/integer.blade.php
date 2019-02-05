@extends('fields.show')

@section('fieldOptions')
    <div class="form-group">
        {!! Form::label('default','Default') !!}
        <span class="error-message"></span>
        <div class="number-input-container number-input-container-js">
            <input type="number" name="default" class="text-input number-default-js" value="{{ $field['default'] }}" placeholder="Enter number here">
        </div>
    </div>

    <div class="form-group mt-xl">
        {!! Form::label('min','Minimum Value') !!}
        <span class="error-message"></span>
        <div class="number-input-container number-input-container-js">
            <input type="number" name="min" class="text-input number-min-js" step="any" id="min"
                value="{{ $field['Min'] }}"
                placeholder = "Enter minimum value here">
        </div>
    </div>

    <div class="form-group mt-xl">
        {!! Form::label('max','Max Value') !!}
        <span class="error-message"></span>
        <div class="number-input-container number-input-container-js">
            <input type="number" name="max" class="text-input number-max-js" step="any" id="max"
                value="{{ $field['Max'] }}"
    			      placeholder="Enter max value here">
        </div>
    </div>

    <div class="form-group mt-xl">
        {!! Form::label('inc','Value Increment') !!}
        <span class="error-message"></span>
        <div class="number-input-container number-input-container-js">
            <input type="number" name="inc" class="text-input number-step-js" step="any" id="inc"
                value="{{ $field['Increment'] }}"
			          placeholder="Enter value increment here">
        </div>
    </div>

    <div class="form-group mt-xl">
        {!! Form::label('unit','Unit of Measurement') !!}
        {!! Form::text('unit', $field['Unit'], ['class' => 'text-input', 'placeholder' => 'Enter unit of measurement here']) !!}
    </div>
@stop

@section('fieldOptionsJS')
    Kora.Fields.Options('Number');
    Kora.Inputs.Number();
@stop
