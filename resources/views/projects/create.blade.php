@extends('app', ['page_title' => 'Create a Project', 'page_class' => 'project-create'])

@section('leftNavLinks')
  @include('partials.menu.static', ['name' => 'New Project'])
@stop

@section('stylesheets')
    <!-- No Additional Stylesheets Necessary -->
@stop

@section('aside-content')
  @include('partials.sideMenu.dashboard', ['openDashboardDrawer' => false, 'openProjectDrawer' => true])
@stop

@section('header')
  <section class="head">
      <a class="back" href="{{ URL::previous() }}"><i class="icon icon-chevron"></i></a>
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
    {!! Form::model($project = new \App\Project, ['url' => 'projects', 'class' => 'create-form center']) !!}
    @include('partials.projects.form',['projectMode' => $projectMode, 'type' => 'create'])
    {!! Form::close() !!}
  </section>
@stop

@section('footer')

@stop

@section('javascripts')
  @include('partials.projects.javascripts')

  <script type="text/javascript">
    var validationUrl = "{{ url('projects/validate') }}";
    var csrfToken = "{{ csrf_token() }}";

    Kora.Projects.Create();
  </script>
@stop
