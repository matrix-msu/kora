@extends('app', ['page_title' => 'Edit Field Value Presets', 'page_class' => 'option-preset-create'])

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
                <span>{{$preset->preset['name']}}</span>
            </h1>
            <p class="description">Edit the preset using the form below.</p>
        </div>
    </section>
@stop

@section('body')
    @include("partials.fields.input-modals")
    @include('partials.fieldValuePresets.deletePresetModal')

    <section class="option-preset-selection center">
        <form method="POST" action="{{ action('FieldValuePresetController@edit', ['pid' => $project->id, 'id' => $preset->id]) }}" class="preset-form">
            <input type="hidden" name="_token" value="{{csrf_token()}}">

            <div class="form-group mt-xl">
                {!! Form::label('name', 'Field Value Preset Name') !!}
                <span class="error-message">{{array_key_exists("name", $errors->messages()) ? $errors->messages()["name"][0] : ''}}</span>
                {!! Form::text('name', $preset->preset['name'], ['class' => 'text-input', 'placeholder' => "Enter the name for the new field value preset here"]) !!}
            </div>

            @if($preset->preset['type'] == 'Regex')
                <div class="form-group mt-xl">
                    <label>Regex: </label>
                    <span class="error-message">{{array_key_exists("preset", $errors->messages()) ? $errors->messages()["preset"][0] : ''}}</span>
                    {!! Form::text('preset', $preset->preset['preset'], ['class' => 'text-input', 'placeholder' => 'Enter text value']) !!}
                </div>
            @elseif($preset->preset['type'] == 'List')
                <div class="form-group specialty-field-group list-input-form-group mt-xxxl">
                    {!! Form::label('options','List Options') !!}
                    <span class="error-message">{{array_key_exists("preset", $errors->messages()) ? $errors->messages()["preset"][0] : ''}}</span>

                    <div class="form-input-container">
                        <p class="directions">Add List Options below, and order them via drag & drop or their arrow icons.</p>

                        <!-- Cards of list options -->
                        <div class="list-option-card-container list-option-card-container-js">
                            @php
                                $values = $preset->preset['preset'];
                            @endphp
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
    @include('partials.fieldValuePresets.javascripts')

    <script type="text/javascript">
        var csrfToken = '{{ csrf_token() }}';
        var deletePresetURL = '{{ action('FieldValuePresetController@delete', ['pid' => $project->id])}}';
        var deleteRedirect = '{{ action('FieldValuePresetController@index', ['pid' => $project->id])}}';
        var validationUrl = "{{ action('FieldValuePresetController@validatePresetFormFields',['pid' => $project->id]) }}";
        Kora.FieldValuePresets.Create();
    </script>
@stop
