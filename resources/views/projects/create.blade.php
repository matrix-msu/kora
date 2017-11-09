@extends('app', ['page_title' => 'Create a Project', 'page_class' => 'project-create'])

@section('stylesheets')
  <!-- No Additional Stylesheets Necessary -->
@stop

@section('header')
  <section class="head">
      <div class="inner-wrap center">
        <h1 class="title">
          <i class="icon icon-new-project"></i>
          <span>New Project</span>
        </h1>
        <p class="description">Fill out the form below, and then select "Create Project"</p>
      </div>
  </section>
@stop

@section('body')
  <section class="create-form center">
    {!! Form::model($project = new \App\Project, ['url' => 'projects']) !!}
    @include('partials.projects.form',['projectMode' => $projectMode, 'type' => 'create'])
    {!! Form::close() !!}
  </section>
@stop

@section('footer')

@stop

@section('javascripts')
  @include('partials.projects.javascripts')

  <script type="text/javascript">
    Kora.Projects.Create();
  </script>
@stop
