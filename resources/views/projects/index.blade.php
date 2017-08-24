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
          <a class="option underline-middle active">Recently Modified</a>
          <a class="option underline-middle">Custom</a>
          <a class="option underline-middle">Alphabetical</a>
          <a class="option underline-middle">Inactive</a>
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
    @foreach($projects as $index=>$project)
      <div class="project {{ $index == 0 ? 'active' : '' }}">
        <div class="header {{ $index == 0 ? 'active' : '' }}">
          <div class="left">
            <div class="move-actions">
              <a class="action move-action-js up-js" href="">
                <i class="icon icon-arrow-up"></i>
              </a>

              <a class="action move-action-js down-js" href="">
                <i class="icon icon-arrow-down"></i>
              </a>
            </div>

            <a class="project-name underline-middle-hover" href="{{action("ProjectController@show",["pid" => $project->pid])}}">
              <span class="name">{{$project->name}}</span>
              <i class="icon icon-arrow-right"></i>
            </a>
          </div>
          <div class="project-toggle-wrap">
            <a href="#" class="project-toggle project-toggle-js">
              <i class="icon icon-chevron {{ $index == 0 ? 'active' : '' }}"></i>
            </a>
          </div>
        </div>

        <div class="content content-js {{ $index == 0 ? 'active' : '' }}">
          <div class="id">
            <span class="attribute">Unique Project ID: </span>
            <span>{{$project->slug}}</span>
          </div>

          <div class="description">
            {{$project->description}}
          </div>

          <div class="admins">
            <span class="attribute">Project Admins: </span>
            @foreach($project->adminGroup()->get() as $adminGroup)
              <span>
                {{$adminGroup->users()->lists("username")->implode("username",", ")}}
              </span>
            @endforeach
          </div>

          <div class="forms">
            <span class="attribute">Project Forms:</span>
            @foreach($project->forms()->get() as $form)
              <span class="form"><a class="form-link underline-middle-hover" href="{{action("FormController@show",["pid" => $project->pid,"fid" => $form->fid])}}">{{$form->name}}</a></span>
            @endforeach
          </div>

          <div class="footer">
            <a class="quick-action underline-middle-hover" href="">
              <i class="icon icon-edit"></i>
              <span>Edit Project Info</span>
            </a>

            <a class="quick-action underline-middle-hover" href="">
              <i class="icon icon-search"></i>
              <span>Search Project Records</span>
            </a>

            <a class="quick-action" href="">
              <span>Go to Project</span>
              <i class="icon icon-arrow-right"></i>
            </a>
          </div>
        </div>
      </div>
    @endforeach
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
@stop
