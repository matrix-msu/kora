@extends('app', ['page_title' => 'Projects'])

@section('stylesheets')
  <!-- No Additional Stylesheets Necessary -->
@stop

@section('header')
  <section class="head">
      <div class="inner-wrap">
        <h1 class="title">
          <i class="icon icon-projects"></i>
          <span>Projects</span>
        </h1>
        <p class="description">Select a project below or create a project to get started.</p>
      </div>
  </section>
@stop

@section('body')
  <section class="filters">
      <div class="underline-middle search search-js">
        <i class="icon icon-search"></i>
        <input type="text" placeholder="Find a Project">
        <i class="icon icon-cancel icon-cancel-js"></i>
      </div>
      <div class="sort-options sort-options-js">
          <!-- <a href="modified" class="option underline-middle">Recently Modified</a> -->
          <a href="#custom" class="option underline-middle underline-middle-hover">Custom</a>
          <a href="#active" class="option underline-middle underline-middle-hover active">Alphabetical</a>
          <a href="#inactive" class="option underline-middle underline-middle-hover">Inactive</a>
      </div>
  </section>
  <section class="new-project-button">
    <form action="{{ action('ProjectController@create') }}">
      @if(\Auth::user()->admin)
        <input type="submit" value="Create a New Project">
      @endif
    </form>
  </section>
  <section class="project-selection project-js project-selection-js">
    @include("partials.projects.active", ['isCustom' => false, 'active' => true])
    @include("partials.projects.inactive", ['isCustom' => false, 'active' => false])
    @include("partials.projects.custom", ['isCustom' => true, 'active' => false])
  </section>
  <section class="foot">
    <p class="permission-information">
        Don't see the project you are looking for? You might not have the permissions...
    </p>
    <p>
    <a href="#" class="request-permissions underline-middle-hover">
        Request Permissions to a Project
    </a></p>
  </section>
@stop

@section('footer')

@stop

@section('javascripts')
  {!! Minify::javascript([
    '/assets/javascripts/vendor/jquery/jquery.js',
    '/assets/javascripts/vendor/jquery/jquery-ui.js',
    '/assets/javascripts/projects/index.js',
    '/assets/javascripts/navigation/navigation.js',
    '/assets/javascripts/general/global.js'
  ])->withFullUrl() !!}

  <script type="text/javascript">
    Kora.Projects.Index();
  </script>
@stop
