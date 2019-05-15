@extends('app', ['page_title' => $field['name'], 'page_class' => 'field-show'])

@section('leftNavLinks')
    @include('partials.menu.project', ['pid' => $form->project_id])
    @include('partials.menu.form', ['pid' => $form->project_id, 'fid' => $form->id])
    @include('partials.menu.static', ['name' => $field['name']])
@stop

@section('aside-content')
  @include('partials.sideMenu.form', ['pid' => $form->project_id, 'fid' => $form->id, 'openDrawer' => true])
@stop

@section('header')
    <section class="head">
        <a class="back" href=""><i class="icon icon-chevron"></i></a>
        <div class="inner-wrap center">
            <h1 class="title">
                <i class="icon icon-field"></i>
                <span>{{$field['name']}}</span>
            </h1>
            <p class="description"><span class="head-field-type">Field Type: </span>{{$field['type']}}</p>
        </div>
    </section>
@stop

@section('body')
    @include("partials.fields.input-modals")
    @yield('presetModal')

    <section class="single-field center">
        {!! Form::model($field,  ['method' => 'PATCH', 'action' => ['FieldController@update', $form->project_id, $form->id, $flid], 'class' => 'edit-form']) !!}
        @include('partials.fields.options', ['field'=>$field, 'pid' => $form->project_id, 'fid' => $form->id])
        {!! Form::close() !!}

        @include('partials.fields.modals.fieldCleanupModal', ['field'=>$field, 'pid' => $form->project_id, 'fid' => $form->id])
    </section>
@stop

@section('footer')

@stop

@section('javascripts')
    <script src="{{ url('assets/javascripts/vendor/ckeditor/ckeditor.js') }}"></script>
    @include('partials.fields.javascripts')

    <script type="text/javascript">
        var validationUrl = "{{ action('FieldController@validateFieldFields', ["pid" => $form->project_id, "fid" =>$form->id, "flid" =>$flid]) }}";
        var createFieldValuePresetURL = "{{action("FieldValuePresetController@createApi", ['pid'=>$form->project_id])}}";
        var currFieldType = '{{$field['type']}}';
        CSRFToken = "{{csrf_token()}}";

        Kora.Fields.Show();

        @yield('fieldOptionsJS')
    </script>
@stop
