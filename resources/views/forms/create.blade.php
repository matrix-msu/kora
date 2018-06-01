@extends('app', ['page_title' => 'Create a Form', 'page_class' => 'form-create'])

@section('leftNavLinks')
    @include('partials.menu.project', ['pid' => $project->pid])
    @include('partials.menu.static', ['name' => 'New Form'])
@stop

@section('stylesheets')
  <!-- No Additional Stylesheets Necessary -->
@stop

@section('aside-content')
  @include('partials.sideMenu.project', ['pid' => $project->pid, 'openDrawer' => true])
@stop

@section('header')
  <section class="head">
      <a class="back" onclick="{{ URL::previous() }}"><i class="icon icon-chevron"></i></a>
      <div class="inner-wrap center">
        <h1 class="title">
          <i class="icon icon-form-new"></i>
          <span>New Form</span>
        </h1>
        <p class="description">Fill out the form below, and then select "Create Form"</p>
      </div>
  </section>
@stop

@section('body')
  <section class="create-form center">
    {!! Form::model($form = new \App\Form, ['url' => 'projects/'.$project->pid, 'class' => 'create-form']) !!}
        @include('partials.forms.form',['submitButtonText' => 'Create Form','pid' => $project->pid, 'type' => 'create'])
    {!! Form::close() !!}
  </section>
@stop

@section('footer')

@stop

@section('javascripts')
  @include('partials.forms.javascripts')

  <script type="text/javascript">
      var validationUrl = "{{ url("projects/$project->pid/forms/validate") }}";
      var csrfToken = "{{ csrf_token() }}";

      Kora.Forms.Create();
  </script>
@stop
