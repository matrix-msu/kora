@extends('app', ['page_title' => 'Create a Field', 'page_class' => 'field-create'])

@section('leftNavLinks')
    @include('partials.menu.project', ['pid' => $form->pid])
    @include('partials.menu.form', ['pid' => $form->pid, 'fid' => $form->fid])
    @include('partials.menu.static', ['name' => 'New Field'])
@stop

@section('stylesheets')
    <link rel="stylesheet" href="{{ config('app.url') }}assets/css/vendor/datetimepicker/jquery.datetimepicker.min.css" />
@stop

@section('header')
    <section class="head">
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

    <section class="create-field center">
        {!! Form::model($field = new \App\Field, ['url' => 'projects/'.$form->pid.'/forms/'.$form->fid,'onsubmit' => 'selectAll()']) !!}
        @include('partials.fields.form', ['submitButtonText' => 'Create Field', 'pid' => $form->pid, 'fid' => $form->fid])
        {!! Form::close() !!}
    </section>
@stop

@section('footer')

@stop

@section('javascripts')
    @include('partials.fields.javascripts')

    <script type="text/javascript">
        advanceCreateURL = "{{ action('FieldAjaxController@getAdvancedOptionsPage',['pid' => $form->pid,'fid'=>$form->fid]) }}";
        csrfToken = "{{ csrf_token() }}";

        Kora.Fields.Create();
    </script>
@stop