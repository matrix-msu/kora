@extends('app', ['page_title' => 'Create a Project', 'page_class' => 'new-project'])

@section('stylesheets')
  <!-- No Additional Stylesheets Necessary -->
@stop

@section('header')
  <section class="head">
      <div class="inner-wrap">
        <h1 class="title">
          <i class="icon icon-new-project"></i>
          <span>New Project</span>
        </h1>
        <p class="description">Fill out the form below, and then select "Create Project"</p>
      </div>
  </section>
@stop

@section('body')

    <hr/>

    {!! Form::model($project = new \App\Project, ['url' => 'projects']) !!}
    @include('projects.form',['submitButtonText' => trans('projects_create.project')])
    {!! Form::close() !!}

    @include('errors.list')
@stop

@section('footer')

@stop
