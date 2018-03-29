@extends('fields.show')

@section('stylesheets')
    <link rel="stylesheet" href="{{ config('app.url') }}assets/css/vendor/datetimepicker/jquery.datetimepicker.min.css" />
@stop

@section('fieldOptions')
    <div class="form-group">
        {!! Form::label('default','Default Value: ') !!}
        <select multiple class="multi-select default-event-js" name="default[]" data-placeholder="Add Events Below">
            @foreach(\App\ScheduleField::getDateList($field) as $opt)
                <option value="{{$opt}}" selected>{{$opt}}</option>
            @endforeach
        </select>
    </div>

    <section class="new-object-button low-margin form-group">
        <input type="button" class="add-new-default-event-js" value="Create New Default Event">
    </section>

    <div class="form-group mt-xl">
        {!! Form::label('start','Start Year: ') !!}
        {!! Form::input('number', 'start', \App\Http\Controllers\FieldController::getFieldOption($field,'Start'),
            ['class' => 'text-input', 'min' => 0, 'max' => 9999]) !!}
    </div>

    <div class="form-group mt-xl">
        {!! Form::label('end','End Year: ') !!}
        {!! Form::input('number', 'end', \App\Http\Controllers\FieldController::getFieldOption($field,'End'),
            ['class' => 'text-input', 'min' => 0, 'max' => 9999]) !!}
    </div>

    <div class="form-group mt-xl">
        {!! Form::label('cal','Calendar Display: ') !!}
        {!! Form::select('cal', ['No' => 'No','Yes' => 'Yes'],
            \App\Http\Controllers\FieldController::getFieldOption($field,'Calendar'), ['class' => 'single-select']) !!}
    </div>
@stop

@section('fieldOptionsJS')
    jQuery('.event-start-time-js').datetimepicker({
        format:'m/d/Y g:i A', inline:true, lang:'en', step: 15,
        minDate:'{{ \App\Http\Controllers\FieldController::getFieldOption($field, 'Start') }}/01/01',
        maxDate:'{{ \App\Http\Controllers\FieldController::getFieldOption($field, 'End') }}/12/31'
    });

    jQuery('.event-end-time-js').datetimepicker({
        format:'m/d/Y g:i A', inline:true, lang:'en', step: 15,
        minDate:'{{ \App\Http\Controllers\FieldController::getFieldOption($field, 'Start') }}/01/01',
        maxDate:'{{ \App\Http\Controllers\FieldController::getFieldOption($field, 'End') }}/12/31'
    });

    Kora.Fields.Options('Schedule');
@stop