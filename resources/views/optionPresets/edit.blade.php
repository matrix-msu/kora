@extends('app', ['page_title' => 'Edit Field Value Presets', 'page_class' => 'option-preset-create'])

@section('stylesheets')
    <link rel="stylesheet" href="{{ url('assets/css/vendor/datetimepicker/jquery.datetimepicker.min.css') }}" />
@stop

@section('aside-content')
    @include('partials.sideMenu.project', ['pid' => $project->pid, 'openDrawer' => true])
@stop

@section('leftNavLinks')
    @include('partials.menu.project', ['pid' => $project->pid])
    @include('partials.menu.fieldValPresets')
@stop

@section('header')
    <section class="head">
        <a class="back" href=""><i class="icon icon-chevron"></i></a>
        <div class="inner-wrap center">
            <h1 class="title">
                <i class="icon icon-preset"></i>
                <span>{{$preset->name}}</span>
            </h1>
            <p class="description">Edit the preset using the form below.</p>
        </div>
    </section>
@stop

@section('body')
    @include("partials.fields.input-modals")
    @include('partials.optionPresets.deletePresetModal')

    <section class="option-preset-selection center">
        <form method="POST" action="{{ action('OptionPresetController@edit', ['pid' => $project->pid, 'id' => $preset->id]) }}" class="preset-form">
            <input type="hidden" name="_token" value="{{csrf_token()}}">

            <div class="form-group mt-xl">
                {!! Form::label('name', 'Field Value Preset Name') !!}
                <span class="error-message">{{array_key_exists("name", $errors->messages()) ? $errors->messages()["name"][0] : ''}}</span>
                {!! Form::text('name', $preset->name, ['class' => 'text-input', 'placeholder' => "Enter the name for the new field value preset here"]) !!}
            </div>

            @if($preset->type == 'Text')
                <div class="form-group mt-xl">
                    <label>Regex: </label>
                    <span class="error-message">{{array_key_exists("preset", $errors->messages()) ? $errors->messages()["preset"][0] : ''}}</span>
                    {!! Form::text('preset', $preset->preset, ['class' => 'text-input', 'placeholder' => 'Enter text value']) !!}
                </div>
            @elseif($preset->type == 'List')
                <div class="form-group specialty-field-group list-input-form-group mt-xxxl">
                    {!! Form::label('options','List Options') !!}
                    <span class="error-message">{{array_key_exists("preset", $errors->messages()) ? $errors->messages()["preset"][0] : ''}}</span>

                    <div class="form-input-container">
                        <p class="directions">Add List Options below, and order them via drag & drop or their arrow icons.</p>

                        <!-- Cards of list options -->
                        <div class="list-option-card-container list-option-card-container-js">
                            <?php
                                $values = explode("[!]", $preset->preset);
                            ?>
                            @foreach($values as $value)
                                <div class="card list-option-card list-option-card-js" data-list-value="{{ $value }}">
                                    <input type="hidden" class="list-option-js" name="preset[]" value="{{ $value }}">

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

                                            <span class="title">{{ $value }}</span>
                                        </div>

                                        <div class="card-toggle-wrap">
                                            <a class="list-option-delete list-option-delete-js tooltip" href="" tooltip="Delete List Option"><i class="icon icon-trash"></i></a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

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
                    </div>
                </div>
            @elseif($preset->type == 'Schedule')
                <?php
                    $values = explode("[!]", $preset->preset);
                    $valuesArray = array();
                    foreach($values as $value) {
                        $valuesArray[$value] = $value;
                    }
                    ?>
                <div class="form-group mt-xl">
                    <label>Locations: </label>
                    <span class="error-message">{{array_key_exists("preset", $errors->messages()) ? $errors->messages()["preset"][0] : ''}}</span>
                    {!! Form::select('preset[]', $valuesArray, $values, ['class' => 'multi-select schedule-event-js',
                        'multiple', 'data-placeholder' => "Add Events Below"]) !!}
                </div>

                <section class="new-object-button form-group mt-sm">
                    <input type="button" class="add-new-default-event-js" value="Create New Event">
                </section>
            @elseif($preset->type == 'Geolocator')
                <?php
                    $values = explode("[!]", $preset->preset);
                    $valuesArray = array();
                    foreach($values as $value) {
                        $valuesArray[$value] = $value;
                    }
                ?>
                <div class="form-group mt-xl">
                    <label>Events: </label>
                    <span class="error-message">{{array_key_exists("preset", $errors->messages()) ? $errors->messages()["preset"][0] : ''}}</span>
                    {!! Form::select('preset[]', $valuesArray, $values, ['class' => 'multi-select geolocator-location-js',
                        'multiple', 'data-placeholder' => "Add Locations Below"]) !!}
                </div>

                <section class="new-object-button form-group mt-xl">
                    <input type="button" class="add-new-default-location-js" value="Create New Location">
                </section>
            @endif


            <div class="form-group mt-xl">
                <label for="required">Share With All Projects?</label>
                <div class="check-box">
                    <input type="checkbox" value="1" id="preset" class="check-box-input" name="shared" @if($preset->shared) checked @endif/>
                    <div class="check-box-background"></div>
                    <span class="check"></span>
                    <span class="placeholder">Select to share with all projects</span>
                    <span class="placeholder-alt">Shared with all projects</span>
                </div>
            </div>

            <div class="form-group mt-xxxl">
                {!! Form::submit('Update Field Value Preset',['class' => 'btn validate-preset-js']) !!}
            </div>
        </form>

        <div class="form-group mt-100-xl mb-max">
            <div class="form-cleanup">
                <a class="btn dot-btn trash warning delete-preset-open-js" data-title="Delete Form?" href="#" preset-id="{{$preset->id}}">
                    <i class="icon icon-trash"></i>
                </a>
            </div>
        </div>
    </section>
@stop

@section('javascripts')
    @include('partials.optionPresets.javascripts')

    <script type="text/javascript">
        var CSRFToken = '{{ csrf_token() }}';
        var geoConvertUrl = '{{ action('FieldAjaxController@geoConvert',['pid' => $project->pid, 'fid' => 0, 'flid' => 0]) }}';
        var deletePresetURL = '{{ action('OptionPresetController@delete', ['pid' => $project->pid])}}';
        var deleteRedirect = '{{ action('OptionPresetController@index', ['pid' => $project->pid])}}';
        var validationUrl = "{{ action('OptionPresetController@validatePresetFormFields',['pid' => $project->pid]) }}";
        Kora.OptionPresets.Create();
    </script>
@stop
