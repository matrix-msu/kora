@extends('app', ['page_title' => 'Edit Record', 'page_class' => 'record-edit'])

@section('leftNavLinks')
    @include('partials.menu.project', ['pid' => $form->project_id])
    @include('partials.menu.form', ['pid' => $form->project_id, 'fid' => $form->id])
    @include('partials.menu.record', ['pid' => $record->project_id, 'fid' => $record->form_id, 'rid' => $record->id])
    @include('partials.menu.static', ['name' => 'Edit Record'])
@stop

@section('aside-content')
  @include('partials.sideMenu.form', ['pid' => $form->project_id, 'fid' => $form->id])
  @include('partials.sideMenu.record', ['pid' => $record->project_id, 'fid' => $record->form_id, 'rid' => $record->id, 'openDrawer' => true])
@stop

@section('stylesheets')
    <link rel="stylesheet" href="{{ url('assets/css/vendor/datetimepicker/jquery.datetimepicker.min.css') }}" />
@stop

@section('header')
    <section class="head">
        <a class="back" href=""><i class="icon icon-chevron"></i></a>
        <div class="inner-wrap center">
            <h1 class="title">
                <i class="icon icon-record-edit"></i>
                <span>Edit Record</span>
            </h1>
            <p class="description">Edit out the form below, and then select “Update Record.” If the form goes to
                multiple pages, use the pagination found at the bottom of each page to navigate to the next.</p>
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

    <section class="filters center">
        @if(!$record->isPreset())
            <div class="record-preset">
                <div class="form-group">
                    <div class="check-box-half">
                        <input type="checkbox" value="1" id="active" class="check-box-input newRecPre-check-js"/>
                        <span class="check"></span>
                        <span class="placeholder">Designate this Record as a Preset?</span>
                    </div>
                </div>
            </div>
        @else
            <div class="already-preset">
                <div class="form-group">
                    <div class="check-box-half pt-xxs">
                        <a class="already-preset-js" href="#">Designated as Preset</a>
                    </div>
                </div>
            </div>
        @endif
        <div class="required-tip">
            <span class="oval-icon"></span>
            <span> = Required Field</span>
        </div>
    </section>

    <section class="create-record center">
        {!! Form::model($record, ['method' => 'PATCH', 'action' => ['RecordController@update',$form->project_id, $form->id, $record->id],
            'enctype' => 'multipart/form-data', 'id' => 'new_record_form', 'class' => 'record-form']) !!}

        <div class="form-group mt-xl newRecPre-record-js hidden">
            {!! Form::label('record_preset_name', 'Record Preset Name') !!}
            <input type="text" name="record_preset_name" class="text-input" placeholder="Add Record Preset Name" disabled>
        </div>

        @include('partials.records.form',['form' => $form, 'editRecord' => true, 'layout' => $form->layout])

        @include('partials.records.pagination-form', ['layout' => $form->layout])

        <div class="form-group record-update-button mt-xxxl">
            {!! Form::submit('Update Record',['class' => 'btn edit-btn update-record-submit pre-fixed-js record-validate-js']) !!}
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
        var getPresetDataUrl = "{{action('RecordPresetController@getData')}}";
        var moveFilesUrl = '{{action('RecordPresetController@moveFilesToTemp')}}';
        var geoConvertUrl = '{{ action('FieldAjaxController@geoConvert',['pid' => $form->project_id, 'fid' => $form->id, 'flid' => 0]) }}';
        var csrfToken = "{{ csrf_token() }}";
        var userID = "{{\Auth::user()->id}}";
        var baseFileUrl = "{{url('deleteTmpFile')}}/";
        var validationUrl = "{{action('RecordController@validateRecord',['pid' => $form->project_id, 'fid' => $form->id])}}";

        Kora.Records.Create();
        Kora.Records.Validate();
    </script>
@stop
