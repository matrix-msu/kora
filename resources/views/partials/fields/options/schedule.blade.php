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

    <div class="form-group mt-xxxl">
        {!! Form::label('eventname','Event Name: ') !!}
        <input type="text" class="text-input event-name-js" id="eventname" maxlength="24"
               placeholder="Enter a descriptive name for your new event"/>
    </div>
    <div class="form-group mt-sm half">
        {!! Form::label('startdatetime','Start Time: ') !!}
        <input type='text' class="text-input event-start-time-js"/>
    </div>
    <div class="form-group mt-sm half">
        {!! Form::label('enddatetime','End Time: ') !!}
        <input type='text' class="text-input event-end-time-js"/>
    </div>
    <div class="form-group mt-m">
        <label for="allday">All Day?</label>
        <div class="check-box">
            <input type="checkbox" value="1" id="preset" class="check-box-input event-allday-js" name="allday" />
            <div class="check-box-background"></div>
            <span class="check"></span>
            <span class="placeholder">Select to set the event as all day</span>
            <span class="placeholder-alt">Event is set to be all day</span>
        </div>

        <p class="sub-text mt-sm">
            Event is assigned to the entire 24 hour period of the day
        </p>
    </div>
    <div class="form-group mt-sm">
        <a href="#" class="btn half-sub-btn extend add-new-event-js">Add Event</a>
    </div>

    <div class="form-group mt-xxxl">
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