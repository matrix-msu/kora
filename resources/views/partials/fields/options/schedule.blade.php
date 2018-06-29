@extends('fields.show')

@section('stylesheets')
    <link rel="stylesheet" href="{{ config('app.url') }}assets/css/vendor/datetimepicker/jquery.datetimepicker.min.css" />
@stop

@section('presetModal')
    @include('partials.fields.fieldValuePresetModals.addEventPresetModal', ['presets' => $presets])
    @include('partials.fields.fieldValuePresetModals.createEventPresetModal')
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
        <div><a href="#" class="field-preset-link open-event-modal-js">Use a Value Preset for these Events</a></div>
        <div class="open-create-regex"><a href="#" class="field-preset-link open-create-event-modal-js right
            @if(empty(\App\ScheduleField::getDateList($field))) disabled tooltip @endif" tooltip="You must submit or update the field before creating a New Value Preset">
                Create a New Value Preset from these Events</a></div>
    </section>

    <div class="form-group mt-xxxl">
        {!! Form::label('start','Start Year: ') !!}
        <span class="error-message"></span>
        {!! Form::input('number', 'start', \App\Http\Controllers\FieldController::getFieldOption($field,'Start'),
            ['class' => 'text-input start-year-js', 'min' => 0, 'max' => 9999]) !!}
    </div>

    <div class="form-group mt-xl">
        {!! Form::label('end','End Year: ') !!}
        <span class="error-message"></span>
        {!! Form::input('number', 'end', \App\Http\Controllers\FieldController::getFieldOption($field,'End'),
            ['class' => 'text-input end-year-js', 'min' => 0, 'max' => 9999]) !!}
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