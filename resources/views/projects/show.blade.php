@extends('app', ['page_title' => $project->name, 'page_class' => 'project-show'])

@section('leftNavLinks')
    @include('partials.menu.project', ['pid' => $project->pid])
@stop

@section('aside-content')
  <div class="drawer-element drawer-element-js">
    <a href="#" class="drawer-toggle drawer-toggle-js">
      <i class="icon icon-dashboard"></i>
      <span>Dashboard</span>
      <i class="icon icon-chevron"></i>
    </a>
    <div class="drawer-content drawer-content-js">
      I am a content
    </div>
  </div>
  <div class="drawer-element drawer-element-js">
    <a href="#" class="drawer-toggle drawer-toggle-js">
      <i class="icon icon-projects"></i>
      <span>Projects</span>
      <i class="icon icon-chevron drawer-content-js"></i>
    </a>
    <div class="drawer-content">
      I am a content
    </div>
  </div>
  <div class="drawer-element drawer-element-js">
    <a href="#" class="drawer-toggle drawer-toggle-js">
      <i class="icon icon-project"></i>
      <span>{{ $project->name }}</span>
      <i class="icon icon-chevron"></i>
    </a>
    <ul class="drawer-content drawer-content-js">
      <li class="content-link head">
        <a href="{{ url('/projects/'.$project->pid) }}">
          <span>Project Home</span>
        </a>
      </li>
      @if (\Auth::user()->admin ||  \Auth::user()->isProjectAdmin(\App\Http\Controllers\ProjectController::getProject($project->pid)))
        <?php
        $allowed_forms = \Auth::user()->allowedForms($project->pid);
        $pid = $project->pid;
        ?>
        <li class="content-link">
          <a href="{{action('FormController@create', ['pid'=>$pid])}}">Create New Form</a>
        </li>

        <li class="content-link">
          <a href="{{action('FormController@importFormView', ['pid'=>$pid])}}">Import Form Setup</a>
        </li>

        @if(sizeof($allowed_forms) > 0 )
          <li class="content-link" id="project-submenu">
            <a href='#' class="drawer-sub-menu-toggle drawer-sub-menu-toggle-js" data-toggle="dropdown">
              <span>Jump to Form</span>
              <i class="icon icon-plus"></i>
            </a>

            <ul class="drawer-deep-menu drawer-deep-menu-js">
              @foreach($allowed_forms as $form)
                <li class="drawer-deep-menu-link">
                  <a href="{{ action('FormController@show', ['pid'=>$project->pid, 'fid' => $form->fid]) }}">{{ $form->name }}</a>
                </li>
              @endforeach
            </ul>
          </li>
        @endif

        <li class="content-link pre-spacer">
          <a href="#">Search Project Records</a>
        </li>

        <li class="spacer"></li>

        <li class="content-link">
          <a href="{{ action('ProjectController@edit', ['pid'=>$pid]) }}">Edit Project Information</a>
        </li>

        <li class="content-link">
          <a href="{{ action('ProjectGroupController@index', ['pid'=>$pid]) }}">Project Permissions</a>
        </li>

        <li class="content-link">
          <a href="{{ action('OptionPresetController@index',['pid' => $pid]) }}">Field Value Presets</a>
        </li>

        <li class="content-link">
          <a href="{{ action('FormController@importFormViewK2',['pid' => $pid]) }}">Kora 2 Scheme Importer</a>
        </li>

        <li class="content-link">
          <a href="{{ action('ExportController@exportProject',['pid' => $pid]) }}">Export Project</a>
        </li>
      @endif
    </ul>
  </div>
@stop

@section('stylesheets')
  <!-- No Additional Stylesheets Necessary -->
@stop

@section('header')
  <section class="head">
      <a class="rotate" href="{{ URL::previous() }}"><i class="icon icon-chevron"></i></a>
      <div class="inner-wrap center">
        <h1 class="title">
          <i class="icon icon-project"></i>
          <span>{{ $project->name }}</span>
          <a href="{{ action('ProjectController@edit',['pid' => $project->pid]) }}" class="head-button">
            <i class="icon icon-edit right"></i>
          </a>
        </h1>
        <p class="identifier">
          <span>Unique Project ID:</span>
          <span>{{ $project->slug }}</span>
        </p>
        <p class="description">{{ $project->slug }}: {{ $project->description }}</p>
      </div>
  </section>
@stop

@section('body')
  <section class="filters center">
      <div class="underline-middle search search-js">
        <i class="icon icon-search"></i>
        <input type="text" placeholder="Find a Form">
        <i class="icon icon-cancel icon-cancel-js"></i>
      </div>
      <div class="sort-options sort-options-js">
          <!-- <a href="modified" class="option underline-middle">Recently Modified</a> -->
          <a href="#custom" class="option underline-middle underline-middle-hover">Custom</a>
          <a href="#active" class="option underline-middle underline-middle-hover active">Alphabetical</a>
      </div>
  </section>

  <section class="new-object-button center">
    @if(\Auth::user()->canCreateForms($project))
      <form action="{{ action('FormController@create', ['pid' => $project->pid]) }}">
          <input type="submit" value="Create a New Form">
      </form>
    @endif
  </section>

  <section class="form-selection center form-js form-selection-js">
    @include("partials.projects.show.alphabetical", ['isCustom' => false, 'active' => true])
    @include("partials.projects.show.custom", ['isCustom' => true, 'active' => false])
  </section>
@stop

@section('footer')

@stop

@section('javascripts')
  @include('partials.projects.javascripts')

  <script type="text/javascript">
    var CSRFToken = '{{ csrf_token() }}';
    var saveCustomOrderUrl = '{{ action('Auth\UserController@saveFormCustomOrder', ['pid' => $project->pid]) }}';

    Kora.Projects.Show();
  </script>
@stop
