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
  @php $empty_state = (count($projects) == 0); @endphp

  @if (\App\Http\Controllers\Auth\UserController::returnUserPrefs('onboarding'))
    @include('partials.onboarding.onboardingModal')
  @endif

  @if (!$empty_state)
  <section class="filters center">
      <div class="underline-middle search search-js">
        <i class="icon icon-search"></i>
        <input type="text" placeholder="Find a Project">
        <i class="icon icon-cancel icon-cancel-js"></i>
      </div>
      <div class="sort-options sort-options-js">
          @php $pref = \App\Http\Controllers\Auth\UserController::returnUserPrefs('proj_tab_selection') @endphp
          <!-- <a href="modified" class="option underline-middle">Recently Modified</a> -->
          <a href="#custom" class="option underline-middle underline-middle-hover {{ $pref == "1" ? 'active' : ''}}">Custom</a> <!-- 2 -->
          <a href="#active" class="option underline-middle underline-middle-hover {{ $pref == "2" ? 'active' : ''}}">Alphabetical</a> <!-- 3 -->
          <a href="#inactive" class="option underline-middle underline-middle-hover">Archived</a> <!-- 1 - this corresponds to 'recently modded' which is not an option? so instead it will be 'archived' for now -->
      </div>
  </section>
  @endif

  @if(Auth::user()->admin)
    <section class="new-object-button center padding-top-medium">
      <form action="{{ action('ProjectController@create') }}">
        <input type="submit" value="Create a New Project">
      </form>
    </section>
  @endif

  <section class="project-selection center project-js project-selection-js">
    @if (!$empty_state)
      @include("partials.projects.index.active", ['isCustom' => false, 'active' => $pref == "2" ? true : false, 'archived' => false])
      @include("partials.projects.index.inactive", ['isCustom' => false, 'active' => false, 'archived' => true])
      @include("partials.projects.index.custom", ['isCustom' => true, 'active' => $pref == "1" ? true : false, 'archived' => false])
    @else
      @include('partials.projects.index.no-projects')
    @endif
  </section>

  @include('partials.user.profileModal')

  @if(!Auth::user()->admin && sizeof($requestableProjects)>0 && !$empty_state)
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
