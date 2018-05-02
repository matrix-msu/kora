@extends('app', ['page_title' => 'Create Field Value Presets', 'page_class' => 'option-preset-create'])

@section('stylesheets')
    <link rel="stylesheet" href="{{ config('app.url') }}assets/css/vendor/datetimepicker/jquery.datetimepicker.min.css" />
@stop

@section('aside-content')
    @include('partials.sideMenu.dashboard')
@stop

@section('leftNavLinks')
    @include('partials.menu.project', ['pid' => $project->pid])
@stop

@section('header')
    <section class="head">
        <a class="rotate" href="{{ URL::previous() }}"><i class="icon icon-chevron"></i></a>
        <div class="inner-wrap center">
            <h1 class="title">
                <i class="icon icon-preset"></i>
                <span>Create a New Field Value Preset</span>
            </h1>
            <p class="description">Fill out the form below, and then select “Create Field Value Preset.”</p>
        </div>
    </section>
@stop

@section('body')
    @include("partials.fields.input-modals")

    <section class="option-preset-selection center">
        <form method="POST" action="{{ action('OptionPresetController@create', ['pid' => $project->pid]) }}" class="preset-form">
            <input type="hidden" name="_token" value="{{csrf_token()}}">

            <div class="form-group mt-xl">
                <span class="error-message">{{array_key_exists("name", $errors->messages()) ? $errors->messages()["name"][0] : ''}}</span>
                {!! Form::label('name', 'Field Value Preset Name') !!}
                {!! Form::text('name', null, ['class' => 'text-input', 'placeholder' => "Enter the name for the new field value preset here"]) !!}
            </div>

            <div class="form-group mt-xl">
                <span class="error-message">{{array_key_exists("type", $errors->messages()) ? $errors->messages()["type"][0] : ''}}</span>
                {!! Form::label('type', 'Field Value Preset Type') !!}
                {!! Form::select('type',
                    [''=>'','Text'=>'Text','List'=>'List','Schedule'=>'Schedule','Geolocator'=>'Geolocator'],
                    null, ['class' => 'single-select preset-type-js','data-placeholder' => "Select the type field value preset here"]) !!}
            </div>

            <section class="open-text-js hidden">
                <div class="form-group mt-xl">
                    <label>Regex: </label>
                    <span class="error-message">{{array_key_exists("preset", $errors->messages()) ? $errors->messages()["preset"][0] : ''}}</span>
                    {!! Form::text('preset', null, ['class' => 'text-input', 'disabled', 'placeholder' => 'Enter text value']) !!}
                </div>
            </section>

            <section class="open-list-js hidden">
                <div class="form-group mt-xl">
                    <label>List Options: </label>
                    <span class="error-message">{{array_key_exists("preset", $errors->messages()) ? $errors->messages()["preset"][0] : ''}}</span>
                    {!! Form::select('preset[]', [], null, ['class' => 'multi-select modify-select', 'multiple', 'disabled',
                        'data-placeholder' => "Enter list value and press enter to submit"]) !!}
                </div>
            </section>

            <section class="open-schedule-js hidden">
                <div class="form-group mt-xl">
                    <label>Events: </label>
                    <span class="error-message">{{array_key_exists("preset", $errors->messages()) ? $errors->messages()["preset"][0] : ''}}</span>
                    {!! Form::select('preset[]', [], null,['class' => 'multi-select schedule-event-js', 'disabled',
                        'multiple', 'data-placeholder' => "Add Events Below"]) !!}
                </div>

                <section class="new-object-button form-group">
                    <input type="button" class="add-new-default-event-js" value="Create New Event">
                </section>
            </section>

            <section class="open-geolocator-js hidden">
                <div class="form-group mt-xl">
                    <label>Locations: </label>
                    <span class="error-message">{{array_key_exists("preset", $errors->messages()) ? $errors->messages()["preset"][0] : ''}}</span>
                    {!! Form::select('preset[]', [], null,['class' => 'multi-select geolocator-location-js', 'disabled',
                        'multiple', 'data-placeholder' => "Add Locations Below"]) !!}
                </div>

                <section class="new-object-button form-group">
                    <input type="button" class="add-new-default-location-js" value="Create New Location">
                </section>
            </section>


            <div class="form-group mt-xl">
                <label for="required">Share With All Projects?</label>
                <div class="check-box">
                    <input type="checkbox" value="1" id="preset" class="check-box-input" name="shared" />
                    <div class="check-box-background"></div>
                    <span class="check"></span>
                    <span class="placeholder">Select to share with all projects</span>
                    <span class="placeholder-alt">Shared with all projects</span>
                </div>
            </div>

            <div class="form-group mt-xxxl mb-max">
                {!! Form::submit('Create Field Value Preset',['class' => 'btn disabled submit-button-js validate-preset-js']) !!}
            </div>
        </form>
    </section>
@stop

@section('javascripts')
    @include('partials.optionPresets.javascripts')

    <script type="text/javascript">
        var CSRFToken = '{{ csrf_token() }}';
        var geoConvertUrl = '{{ action('FieldAjaxController@geoConvert',['pid' => $project->pid, 'fid' => 0, 'flid' => 0]) }}';
        var validationUrl = "{{ action('OptionPresetController@validatePresetFormFields',['pid' => $project->pid]) }}";

        Kora.OptionPresets.Create();
    </script>
@stop