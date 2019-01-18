@extends('app', ['page_title' => 'Projects', 'page_class' => 'projects'])

@section('stylesheets')
  <!-- No Additional Stylesheets Necessary -->
@stop

@section('aside-content')
  @include('partials.sideMenu.dashboard', ['openDashboardDrawer' => false, 'openProjectDrawer' => true])
@stop

@section('header')
  <section class="head">
      <a class="back" href=""><i class="icon icon-chevron"></i></a>
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
  @include('partials.projects.notification')

  @if (\App\Http\Controllers\Auth\UserController::returnUserPrefs('onboarding'))
    @include('partials.onboarding.onboardingModal')
  @endif

  @if (count($projects) > 0 or count($inactive) > 0)
  <section class="filters center">
      <div class="underline-middle search search-js">
        <i class="icon icon-search"></i>
        <input type="text" placeholder="Find a Project">
        <i class="icon icon-cancel icon-cancel-js"></i>
      </div>
      <div class="sort-options sort-options-js">
          @php $pref = 'proj_page_tab_selection' @endphp
          <!-- <a href="modified" class="option underline-middle">Recently Modified</a> -->
          <a href="#custom" class="option underline-middle underline-middle-hover {{ \App\Http\Controllers\Auth\UserController::returnUserPrefs($pref) == "2" ? 'active' : ''}}">Custom</a> <!-- 2 -->
          <a href="#active" class="option underline-middle underline-middle-hover {{ \App\Http\Controllers\Auth\UserController::returnUserPrefs($pref) == "3" ? 'active' : ''}}">Alphabetical</a> <!-- 3 -->
          <a href="#inactive" class="option underline-middle underline-middle-hover {{ \App\Http\Controllers\Auth\UserController::returnUserPrefs($pref) == "1" ? 'active' : ''}}">Archived</a> <!-- 1 - this corresponds to 'recently modded' which is not an option? so instead it will be 'archived' for now -->
      </div>
  </section>
  @endif

  <section class="new-object-button center">
    <form action="{{ action('ProjectController@create') }}">
      @if(\Auth::user()->admin)
        <input type="submit" value="Create a New Project">
      @endif
    </form>
  </section>

  <section class="project-selection center project-js project-selection-js">
    @if ( count($projects) > 0 )
    
      @include("partials.projects.index.active", ['isCustom' => false, 'active' => \App\Http\Controllers\Auth\UserController::returnUserPrefs($pref) == "3" ? true : false, 'archived' => false])
      @include("partials.projects.index.inactive", ['isCustom' => false, 'active' => \App\Http\Controllers\Auth\UserController::returnUserPrefs($pref) == "1" ? true : false, 'archived' => true])
      @include("partials.projects.index.custom", ['isCustom' => true, 'active' => \App\Http\Controllers\Auth\UserController::returnUserPrefs($pref) == "2" ? true : false, 'archived' => false])
    
    @else
      @include('partials.projects.index.no-projects')
    @endif
  </section>

  @include('partials.user.profileModal')

  @if(!Auth::user()->admin && sizeof($requestableProjects)>0)
    <section class="foot center">
      <p class="permission-information">
          Don't see the project you are looking for? You might not have the permissions...
      </p>
      <p>
      <a href="#" class="request-permissions project-request-perms-js underline-middle-hover">
          Request Permissions to a Project
      </a></p>
    </section>
  @endif
@stop

@section('footer')

@stop

@section('javascripts')
  @include('partials.projects.javascripts')

  <script type="text/javascript">
    var CSRFToken = '{{ csrf_token() }}';
    var saveCustomOrderUrl = '{{ action('Auth\UserController@saveProjectCustomOrder') }}';
    var archiveURL = '{{ action('ProjectController@setArchiveProject', ['pid' => ""] ) }}';
    var updateURL = '{{ action('UpdateController@index') }}';
    Kora.Projects.Index();
  </script>

	@if (\App\Http\Controllers\Auth\UserController::returnUserPrefs('onboarding'))
		<script> var toggleOnboardingUrl = '{{ action('Auth\UserController@toggleOnboarding') }}'; </script>
		<script src="{{ url('/assets/javascripts/general/onboarding.js') }}"></script>
	@endif
@stop
