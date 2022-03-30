@extends('app', ['page_title' => 'Duplicate Record', 'page_class' => 'record-clone'])

@section('leftNavLinks')
    @include('partials.menu.project', ['pid' => $form->project_id])
    @include('partials.menu.form', ['pid' => $form->project_id, 'fid' => $form->id])
    @include('partials.menu.record', ['pid' => $record->project_id, 'fid' => $record->form_id, 'rid' => $record->id])
    @include('partials.menu.static', ['name' => 'Duplicate Record'])
@stop

@section('aside-content')
  @include('partials.sideMenu.form', ['pid' => $form->project_id, 'fid' => $form->id])
  @include('partials.sideMenu.record', ['pid' => $record->project_id, 'fid' => $record->form_id, 'rid' => $record->id, 'openDrawer' => true])
@stop

@section('stylesheets')

@stop

@section('header')
    <section class="head">
        <a class="back" href=""><i class="icon icon-chevron"></i></a>
        <div class="inner-wrap center">
            <h1 class="title">
                <i class="icon icon-duplicate"></i>
                <span>Duplicate Record</span>
            </h1>
            <p class="description">Adjust the number of duplicates you wish to make for record: {{$record->kid}}. Then adjust
                the record below as needed. Adjustments you make here will only be applied to the new duplicate record(s).</p>
            <div class="form-group mt-xl mb-xxl duplicate-record-special-js">
                {!! Form::label('mass_creation_num', 'Select duplication amount (max 1000): ') !!}
                <div class="number-input-container number-input-container-js">
                  <input type="number" name="mass_creation_num" class="text-input" value="1" step="1" max="1000" min="1">
                </div>
            </div>
            <div class="content-sections">
                <div class="content-sections-scroll">
                  @foreach($form->layout['pages'] as $page)
                    <a href="#{{$page["title"]}}" class="section underline-middle underline-middle-hover toggle-by-name">{{$page["title"]}}</a>
                  @endforeach
                </div>
            </div>
        </div>
    </section>
@stop

@section('body')
    @include("partials.fields.input-modals")
    @include("partials.fields.validationModal")

    <section class="filters center">
        <div class="required-tip">
            <span class="oval-icon"></span>
            <span> = Required Field</span>
        </div>
    </section>

    <section class="create-record center">
        {!! Form::model($cloneRecord = new \App\Record, ['url' => 'projects/'.$form->project_id.'/forms/'.$form->id.'/records',
            'enctype' => 'multipart/form-data', 'id' => 'new_record_form']) !!}

        <div class="form-group mt-xxxl duplicate-record-js hidden">
            {!! Form::label('mass_creation_num', 'Select duplication amount (max 1000): ') !!}
            <div class="number-input-container number-input-container-js">
                <input type="number" name="mass_creation_num" class="text-input" value="1" step="1" max="1000" min="1">
            </div>
        </div>

        @include('partials.records.form',['form' => $form, 'editRecord' => true, 'layout' => $form->layout])

        <div class="form-group mt-xxxl">
            <div class="spacer"></div>
        </div>

        <div class="form-group mt-xxxl">
            <div class="check-box-half">
                <input type="checkbox" value="1" id="active" class="check-box-input newRecPre-check-js" />
                <span class="check"></span>
                <span class="placeholder">Create New Record Preset from this Record</span>
            </div>

            <p class="sub-text mt-sm">
                This will create a new record preset from this record.
            </p>
        </div>

        <div class="form-group mt-xl newRecPre-record-js hidden">
            {!! Form::label('record_preset_name', 'Record Preset Name') !!}
            <input type="text" name="record_preset_name" class="text-input" placeholder="Add Record Preset Name" disabled>
        </div>

        <div class="form-group mt-100-xl">
            {!! Form::submit('Duplicate Record',['class' => 'btn fixed-bottom-slide pre-fixed-js clone-btn']) !!}
        </div>
        {!! Form::close() !!}
    </section>
@stop

@section('footer')

@stop

@section('javascripts')
    @include('partials.records.javascripts')

    <script src="{{ url('assets/javascripts/vendor/ckeditor/ckeditor.js') }}"></script>

    <script type="text/javascript">
        getPresetDataUrl = "{{action('RecordPresetController@getData')}}";
        moveFilesUrl = '{{action('RecordPresetController@moveFilesToTemp')}}';
        geoConvertUrl = '{{ action('FieldAjaxController@geoConvert',['pid' => $form->project_id, 'fid' => $form->id, 'flid' => 0]) }}';
        csrfToken = "{{ csrf_token() }}";
        userID = "{{\Auth::user()->id}}";
        var tmpFileDir = "{{$tmpFileDir}}";
        baseFileUrl = "{{url('deleteTmpFile')}}/";
        var validationUrl = "{{action('RecordController@validateRecord',['pid' => $form->project_id, 'fid' => $form->id])}}";

        Kora.Records.Create();
        Kora.Records.Validate();
    </script>
@stop
