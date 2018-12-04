@extends('fields.show')

@section('stylesheets')
    <link rel="stylesheet" href="{{ url('assets/css/vendor/datetimepicker/jquery.datetimepicker.min.css') }}" />
@stop

@section('presetModal')
    @include('partials.fields.fieldValuePresetModals.addEventPresetModal', ['presets' => $presets])
    @include('partials.fields.fieldValuePresetModals.createEventPresetModal')
@stop

@section('fieldOptions')
    <div class="form-group schedule-form-group schedule-form-group-js schedule-{{$field->flid}}-js mt-xxxl">
        {!! Form::label('default','Default Events') !!}
        <div class="form-input-container">
            <p class="directions">Add Default Events below, and order them via drag & drop or their arrow icons.</p>

            <div class="schedule-card-container schedule-card-container-js mb-xxl">
                @foreach(\App\ScheduleField::getDateList($field) as $opt)
                    <?php
                    $event = explode(': ', $opt, 2);
                    $name = $event[0];
                    $times = $event[1];
                    ?>
                    <div class="card schedule-card schedule-card-js">
                        <input type="hidden" class="list-option-js" name="default[]" value="{{$opt}}'">
                        <div class="header">
                            <div class="left">
                                <div class="move-actions">
                                    <a class="action move-action-js up-js" href="">
                                        <i class="icon icon-arrow-up"></i>
                                    </a>
                                    <a class="action move-action-js down-js" href="">
                                        <i class="icon icon-arrow-down"></i>
                                    </a>
                                </div>
                                <span class="title">{{$name}}</span>
                            </div>
                            <div class="card-toggle-wrap">
                                <a class="schedule-delete schedule-delete-js tooltip" tooltip="Delete Event" href=""><i class="icon icon-trash"></i></a>
                            </div>
                        </div>
                        <div class="content"><p class="event-time">{{$times}}</p></div>
                    </div>
                @endforeach
            </div>

            <section class="new-object-button">
                <input class="add-new-default-event-js" type="button" value="Create New Default Event">
            </section>
        </div>
    </div>

    <section class="form-group">
        <div><a href="#" class="field-preset-link open-event-modal-js">Use a Value Preset for these Events</a></div>
        <div class="open-create-regex"><a href="#" class="field-preset-link open-create-event-modal-js right
            @if(empty(\App\ScheduleField::getDateList($field))) disabled tooltip @endif" tooltip="You must submit or update the field before creating a New Value Preset">
                Create a New Value Preset from these Events</a></div>
    </section>

    <div class="form-group half mt-xxxl pr-sm">
        {!! Form::label('start','Start Year') !!}
        <span class="error-message"></span>
        <div class="number-input-container number-input-container-js">
            {!! Form::input('number', 'start', \App\Http\Controllers\FieldController::getFieldOption($field,'Start'),
                ['class' => 'text-input start-year-js', 'min' => 0, 'max' => 9999, 'placeholder' => 'Enter start year here']) !!}
        </div>
    </div>

    <div class="form-group half mt-xxxl pl-sm">
        {!! Form::label('end','End Year') !!}
        <span class="error-message"></span>
        <div class="number-input-container number-input-container-js">
            {!! Form::input('number', 'end', \App\Http\Controllers\FieldController::getFieldOption($field,'End'),
                ['class' => 'text-input end-year-js', 'min' => 0, 'max' => 9999, 'placeholder' => 'Enter end year here']) !!}
        </div>
    </div>

    <div class="form-group mt-xl">
        {!! Form::label('cal','Calendar Display') !!}
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