@extends('app', ['page_title' => 'Projects', 'page_class' => 'projects'])

@section('stylesheets')
  <!-- No Additional Stylesheets Necessary -->
@stop

@section('header')
  <section class="head">
      <a class="rotate" href="{{ URL::previous() }}"><i class="icon icon-chevron"></i></a>
      <div class="inner-wrap center">
        <h1 class="title">
          <i class="icon icon-projects"></i>
          <span>Projects</span>
        </h1>
        <p class="description">Select a project below or create a project to get started.</p>
      </div>
  </section>
@stop

@section('body')
  <section class="filters center">
      <div class="underline-middle search search-js">
        <i class="icon icon-search"></i>
        <input type="text" placeholder="Find a Project">
        <i class="icon icon-cancel icon-cancel-js"></i>
      </div>
      <div class="sort-options sort-options-js">
          <!-- <a href="modified" class="option underline-middle">Recently Modified</a> -->
          <a href="#custom" class="option underline-middle underline-middle-hover">Custom</a>
          <a href="#active" class="option underline-middle underline-middle-hover active">Alphabetical</a>
          <a href="#inactive" class="option underline-middle underline-middle-hover">Archived</a>
      </div>
  </section>

  <section class="new-object-button center">
    <form action="{{ action('ProjectController@create') }}">
      @if(\Auth::user()->admin)
        <input type="submit" value="Create a New Project">
      @endif
    </form>
  </section>

  <section class="project-selection center project-js project-selection-js">
    @include("partials.projects.index.active", ['isCustom' => false, 'active' => true, 'archived' => false])
    @include("partials.projects.index.inactive", ['isCustom' => false, 'active' => false, 'archived' => true])
    @include("partials.projects.index.custom", ['isCustom' => true, 'active' => false, 'archived' => false])
  </section>

  <section class="foot center">
    <p class="permission-information">
        Don't see the project you are looking for? You might not have the permissions...
    </p>
    <p>
    <a href="#" class="request-permissions request-permissions-js underline-middle-hover">
        Request Permissions to a Project
    </a></p>
    <div class="modal modal-js modal-mask request-permissions-modal-js">
      <div class="content">
        <div class="header">
          <span class="title">Request Project Permissions</span>
          <a href="#" class="modal-toggle modal-toggle-js">
            <i class="icon icon-cancel"></i>
          </a>
        </div>
        <div class="body">
          @include("partials.projects.projectRequestModalForm")
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
    var CSRFToken = '{{ csrf_token() }}';
    var saveCustomOrderUrl = '{{ action('Auth\UserController@saveProjectCustomOrder') }}';
    Kora.Projects.Index();
  </script>
@stop
