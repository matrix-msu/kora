@extends('app', ['page_title' => 'Create Field Value Presets', 'page_class' => 'option-preset-create'])

@section('stylesheets')

@stop

@section('aside-content')
    @include('partials.sideMenu.project', ['pid' => $project->id, 'openDrawer' => true])
@stop

@section('leftNavLinks')
    @include('partials.menu.project', ['pid' => $project->id])
    @include('partials.menu.fieldValPresets')
@stop

@section('header')
    <section class="head">
        <a class="back" href=""><i class="icon icon-chevron"></i></a>
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
        <form method="POST" action="{{ action('FieldValuePresetController@create', ['pid' => $project->id]) }}" class="preset-form">
            <input type="hidden" name="_token" value="{{csrf_token()}}">

            <div class="form-group mt-xl">
                <span class="error-message">{{array_key_exists("name", $errors->messages()) ? $errors->messages()["name"][0] : ''}}</span>
                {!! Form::label('name', 'Field Value Preset Name') !!}
                {!! Form::text('name', null, ['class' => 'text-input', 'placeholder' => "Enter the name for the new field value preset here"]) !!}
            </div>

            <div class="form-group mt-xl">
                <span class="error-message">{{array_key_exists("type", $errors->messages()) ? $errors->messages()["type"][0] : ''}}</span>
                {!! Form::label('type', 'Field Value Preset Type') !!}
                {!! Form::select('type', [''=>'','Regex'=>'Regex','List'=>'List'],
                    null, ['class' => 'single-select preset-type-js','data-placeholder' => "Select the type field value preset here"]) !!}
            </div>

            <section class="open-regex-js hidden">
                <div class="form-group mt-xl">
                    <label>Regex: </label>
                    <span class="error-message">{{array_key_exists("preset", $errors->messages()) ? $errors->messages()["preset"][0] : ''}}</span>
                    {!! Form::text('preset', null, ['class' => 'text-input', 'disabled', 'placeholder' => 'Enter text value']) !!}
                </div>
            </section>

            <section class="open-list-js hidden">
                <div class="form-group specialty-field-group list-input-form-group mt-xxxl">
                    {!! Form::label('options','List Options') !!}
                    <span class="error-message">{{array_key_exists("preset", $errors->messages()) ? $errors->messages()["preset"][0] : ''}}</span>

                    <div class="form-input-container">
                        <p class="directions">Add List Options below, and order them via drag & drop or their arrow icons.</p>

                        <!-- Cards of list options -->
                        <div class="list-option-card-container list-option-card-container-js"></div>

                        <!-- Card to add list options -->
                        <div class="card new-list-option-card new-list-option-card-js">
                            <div class="header">
                                <div class="left">
                                    <input class="new-list-option new-list-option-js" type="text" placeholder='Type here and hit the enter key or "Add" to add new list options'>
                                </div>

                                <div class="card-toggle-wrap">
                                    <a class="list-option-add list-option-add-js" href=""><span>Add</span></a>
                                </div>
                            </div>
                        </div>

                        <div class="list-option-mass-opts mt-xl mb-xs">
                            <div class="list-option-mass-link list-option-mass-copy">
                                <i class="icon icon-duplicate-little"></i>
                                <a href="#" class="list-option-mass-copy-js">Copy All List Options</a>
                            </div>
                            <div class="list-option-mass-link list-option-mass-delete right">
                                <i class="icon icon-trash"></i>
                                <a href="#" class="list-option-mass-delete-js">Delete All List Options</a>
                            </div>
                        </div>
                    </div>
                </div>
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
    @include('partials.fieldValuePresets.javascripts')

    <script type="text/javascript">
        var csrfToken = '{{ csrf_token() }}';
        var validationUrl = "{{ action('FieldValuePresetController@validatePresetFormFields',['pid' => $project->id]) }}";

        Kora.FieldValuePresets.Create();
    </script>
@stop
