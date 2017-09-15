@extends('app', ['page_title' => "Editing {$project->name}", 'page_class' => 'edit-project'])

@section('stylesheets')
  <!-- No Additional Stylesheets Necessary -->
@stop

@section('header')
  <section class="head">
      <div class="inner-wrap center">
        <h1 class="title">
          <i class="icon icon-project-edit"></i>
          <span>Edit Project</span>
        </h1>
        <p class="description">Edit the project information below, and then select “Update Project”</p>
      </div>
  </section>
@stop

@section('body')
  <section class="edit-form center">
    {!! Form::model($project,  ['method' => 'PATCH', 'action' => ['ProjectController@update', $project->pid]]) !!}
    @include('projects.form',['projectMode' => $projectMode])
    {!! Form::close() !!}

    <div class="modal modal-js modal-mask project-cleanup-modal-js">
      <div class="content small">
        <div class="header">
          <span class="title title-js"></span>
          <a href="#" class="modal-toggle modal-toggle-js">
            <i class="icon icon-cancel"></i>
          </a>
        </div>
        <div class="body">
          @include("partials.projects.projectArchiveForm")
          @include("partials.projects.projectDeleteForm")
        </div>
      </div>
    </div>
  </section>
@stop

@section('footer')

@stop

@section('javascripts')
  @include('partials.projects.javascripts')

  <script type="text/javascript">
    Kora.Projects.Edit();
  </script>
@stop
