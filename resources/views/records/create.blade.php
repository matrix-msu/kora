@extends('app', ['page_title' => 'Create a Record', 'page_class' => 'record-create'])

@section('leftNavLinks')
    @include('partials.menu.project', ['pid' => $form->pid])
    @include('partials.menu.form', ['pid' => $form->pid, 'fid' => $form->fid])
    @include('partials.menu.static', ['name' => 'New Record'])
@stop

@section('stylesheets')
    <link rel="stylesheet" href="{{ config('app.url') }}assets/css/vendor/datetimepicker/jquery.datetimepicker.min.css" />
@stop

@section('header')
    <section class="head">
        <div class="inner-wrap center">
            <h1 class="title">
                <i class="icon icon-record-new"></i>
                <span>New Record</span>
            </h1>
            <p class="description">Fill out the form below, and then select “Create New Record.” If the form goes to
                multiple pages, use the pagination found at the bottom of each page to navigate to the next.</p>
            <div class="content-sections">
                @foreach(\App\Http\Controllers\PageController::getFormLayout($form->fid) as $page)
                    <a href="#{{$page["title"]}}" class="section underline-middle underline-middle-hover toggle-by-name">{{$page["title"]}}</a>
                @endforeach
            </div>
        </div>
    </section>
@stop

@section('body')
    @include("partials.fields.input-modals")

    <section class="filters center">
        <div class="record-preset">
            <div class="form-group">
                <div class="check-box-half">
                    <input type="checkbox" value="1" id="active" class="check-box-input preset-check-js" name="active" />
                    <span class="check"></span>
                    <span class="placeholder">Use a Record Preset</span>
                </div>
            </div>
        </div>
        <div class="required-tip">
            <span class="oval-icon"></span>
            <span> = Required Field</span>
        </div>
    </section>

    <section class="create-record center">
        <div class="form-group mt-xl preset-record-div-js hidden">
            <label>{!! Form::label('presetlabel', 'Select a Preset: ') !!}</label>
            <select class="single-select preset-record-js">
                <option disabled selected>Select a Record Preset</option>
                @for($i=0; $i < sizeof($presets); $i++)
                    <option value="{{$presets[$i]['id']}}">{{$presets[$i]['name']}}</option>
                @endfor
            </select>
        </div>

        {!! Form::model($record = new \App\Record, ['url' => 'projects/'.$form->pid.'/forms/'.$form->fid.'/records',
            'enctype' => 'multipart/form-data', 'id' => 'new_record_form']) !!}
            @include('partials.records.form',['form' => $form])

            <div class="form-group mt-xxxl">
                <div class="spacer"></div>
            </div>

            <div class="form-group mt-xxxl">
                <div class="check-box-half">
                    <input type="checkbox" value="1" id="active" class="check-box-input duplicate-check-js" />
                    <span class="check"></span>
                    <span class="placeholder">Duplicate Record</span>
                </div>

                <p class="sub-text mt-sm">
                    This will create multiples of this record. You can set the number of duplicates after selecting.
                </p>
            </div>

            <div class="form-group mt-xl duplicate-record-js hidden">
                {!! Form::label('mass_creation_num', 'Select duplication amount (max 1000): ') !!}
                <input type="number" name="mass_creation_num" class="text-input" value="2" step="1" max="1000" min="2" disabled>
            </div>

            <div class="form-group mt-xxxl">
                <div class="check-box-half">
                    <input type="checkbox" value="1" id="active" class="check-box-input newRecPre-check-js" />
                    <span class="check"></span>
                    <span class="placeholder">Create New Record Preset from this New Record</span>
                </div>

                <p class="sub-text mt-sm">
                    This will create a new record preset from this new record.
                </p>
            </div>

            <div class="form-group mt-xl newRecPre-record-js hidden">
                {!! Form::label('record_preset_name', 'Record Preset Name: ') !!}
                <input type="text" name="record_preset_name" class="text-input" placeholder="Add Record Preset Name" disabled>
            </div>

            <div class="form-group mt-xxxl">
                {!! Form::submit('Create New Record',['class' => 'btn']) !!}
            </div>
        {!! Form::close() !!}
    </section>
@stop

@section('footer')

@stop

@section('javascripts')
    @include('partials.records.javascripts')

    <script src="{{ config('app.url') }}assets/javascripts/vendor/ckeditor/ckeditor.js"></script>

    <script type="text/javascript">
        getPresetDataUrl = "{{action('RecordPresetController@getData')}}";
        moveFilesUrl = '{{action('RecordPresetController@moveFilesToTemp')}}';
        geoConvertUrl = '{{ action('FieldAjaxController@geoConvert',['pid' => $form->pid, 'fid' => $form->fid, 'flid' => 0]) }}';
        csrfToken = "{{ csrf_token() }}";
        userID = "{{\Auth::user()->id}}";
        baseFileUrl = "{{config('app.url'). 'deleteTmpFile/'}}";

        Kora.Records.Create();
    </script>
@stop