@extends('app', ['page_title' => 'Create a Project', 'page_class' => 'new-project'])

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
    @include('projects.form',['projectMode' => $projectMode])
    {!! Form::close() !!}
  </section>
@stop

@section('footer')

@stop

@section('javascripts')
  {!! Minify::javascript([
    '/assets/javascripts/vendor/jquery/jquery.js',
    '/assets/javascripts/vendor/jquery/jquery-ui.js',
    '/assets/javascripts/vendor/chosen.js',
    '/assets/javascripts/general/modal.js',
    '/assets/javascripts/projects/create.js',
    '/assets/javascripts/projects/index.js',
    '/assets/javascripts/projects/show.js',
    '/assets/javascripts/navigation/navigation.js',
    '/assets/javascripts/general/global.js'
  ])->withFullUrl() !!}

  <script type="text/javascript">
    Kora.Projects.Create();
  </script>
@stop
