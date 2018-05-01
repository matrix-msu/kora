<?php $project = \App\Http\Controllers\ProjectController::getProject($pid) ?>
@include('partials.sideMenu.dashboard')
<div class="drawer-element drawer-element-js">
  <a href="#" class="drawer-toggle drawer-toggle-js">
    <i class="icon icon-project"></i>
    <span>{{ $project->name }}</span>
    <i class="icon icon-chevron"></i>
  </a>
  <ul class="drawer-content drawer-content-js">
    <li class="content-link head">
      <a href="{{ url('/projects/'.$pid) }}">
        <span>Project Home</span>
      </a>
    </li>
    @if (\Auth::user()->admin ||  \Auth::user()->isProjectAdmin(\App\Http\Controllers\ProjectController::getProject($pid)))
      <?php
      $allowed_forms = \Auth::user()->allowedForms($pid);
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
                <a href="{{ action('FormController@show', ['pid'=>$pid, 'fid' => $form->fid]) }}">{{ $form->name }}</a>
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
