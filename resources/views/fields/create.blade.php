@extends('app', ['page_title' => 'Create a Field', 'page_class' => 'field-create'])

@section('leftNavLinks')
    @include('partials.menu.project', ['pid' => $form->project_id])
    @include('partials.menu.form', ['pid' => $form->project_id, 'fid' => $form->id])
    @include('partials.menu.static', ['name' => 'New Field'])
@stop

@section('aside-content')
  @include('partials.sideMenu.form', ['pid' => $form->project_id, 'fid' => $form->id, 'openDrawer' => true])
@stop

@section('stylesheets')
    <link rel="stylesheet" href="{{ url('assets/css/vendor/datetimepicker/jquery.datetimepicker.min.css') }}" />
@stop

@section('header')
    <section class="head">
        <a class="back" href=""><i class="icon icon-chevron"></i></a>
        <div class="inner-wrap center">
            <h1 class="title">
                <i class="icon icon-field-new"></i>
                <span>New Field</span>
            </h1>
            <p class="description">Fill out the form below, and then select "Create Field"</p>
        </div>
    </section>
@stop

@section('body')
    @include("partials.fields.input-modals")
    @include("partials.fields.modals.changeAdvancedFieldModal")
    @include("partials.fields.modals.fieldTypeDescriptionsModal")

    <section class="create-field center">
        <form method="POST" action="{{ action('FieldController@store', ['pid' => $form->project_id, 'fid' => $form->id]) }}" class="create-form">
            <input type="hidden" name="_token" value="{{csrf_token()}}">
            @include('partials.fields.form', ['submitButtonText' => 'Create Field', 'pid' => $form->project_id, 'fid' => $form->id])
        </form>
    </section>
@stop

@section('footer')

@stop

@section('javascripts')
    <script src="{{ url('assets/javascripts/vendor/ckeditor/ckeditor.js') }}"></script>
    @include('partials.fields.javascripts')

    <script type="text/javascript">
        var validationUrl = "{{ url("projects/$form->project_id/forms/$form->id/fields/validate") }}";
        advanceCreateURL = "{{ action('FieldAjaxController@getAdvancedOptionsPage',['pid' => $form->project_id,'fid'=>$form->id]) }}";
        csrfToken = "{{ csrf_token() }}";

        Kora.Fields.Create();
    </script>
@stop
