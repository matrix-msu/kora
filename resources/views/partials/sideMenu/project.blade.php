@php
  $project = \App\Http\Controllers\ProjectController::getProject($pid);
@endphp
@include('partials.sideMenu.dashboard')
<div class="drawer-element drawer-element-js">
  <a href="#" class="drawer-toggle drawer-toggle-js" data-drawer="{{ $openDrawer or '0' }}">
    <i class="icon icon-project"></i>
    <span>{{ $project->name }}</span>
    <i class="icon icon-chevron"></i>
  </a>
  <ul class="drawer-content drawer-content-js">
    <li class="content-link content-link-js" data-page="project-show">
      <a href="{{ url('/projects/'.$pid) }}">
        <span>Project Home</span>
      </a>
    </li>
    @if (\Auth::user()->admin ||  \Auth::user()->isProjectAdmin(\App\Http\Controllers\ProjectController::getProject($pid)))
      <?php
      $allowed_forms = \Auth::user()->allowedForms($pid);
      ?>
      <li class="content-link content-link-js" data-page="form-create">
        <a href="{{action('FormController@create', ['pid'=>$pid])}}">Create New Form</a>
      </li>

      <li class="content-link content-link-js" data-page="form-import-setup">
        <a href="{{action('FormController@importFormView', ['pid'=>$pid])}}">Import Form Setup</a>
      </li>

      @if(sizeof($allowed_forms) > 0 )
        <li class="content-link content-link-js" id="project-submenu">
          <a href='#' class="drawer-sub-menu-toggle drawer-sub-menu-toggle-js" data-toggle="dropdown">
            <span>Jump to Form</span>
            <i class="icon icon-plus"></i>
          </a>
		  
		  <?php
		  // Sort forms by name
		  $name_fid_forms = [];
		  
		  foreach ($allowed_forms as $form)
		  {
		    $name_fid_forms[$form->id] = $form->name;
		  }
		  
		  asort($name_fid_forms, SORT_NATURAL | SORT_FLAG_CASE);
		  ?>

          <ul class="drawer-deep-menu drawer-deep-menu-js">
            @foreach($name_fid_forms as $form_fid => $form_name)
              <li class="drawer-deep-menu-link">
                <a href="{{ action('FormController@show', ['pid'=>$pid, 'fid' => $form_fid]) }}">{{ $form_name }}</a>
              </li>
            @endforeach
          </ul>
        </li>
      @endif

      <li class="content-link content-link-js pre-spacer" data-page="project-records">
        <a href="{{ action('ProjectSearchController@keywordSearch', ['pid'=>$pid]) }}">Project Records Search</a>
      </li>

      <li class="spacer"></li>

      <li class="content-link content-link-js" data-page="project-edit">
        <a href="{{ action('ProjectController@edit', ['pid'=>$pid]) }}">Edit Project Information</a>
      </li>

      <li class="content-link content-link-js" data-page="project-permissions">
        <a href="{{ action('ProjectGroupController@index', ['pid'=>$pid]) }}">Project Permissions</a>
      </li>

      <li class="content-link content-link-js" data-page="option-presets">
        <a href="{{ action('FieldValuePresetController@index',['pid' => $pid]) }}">Field Value Presets</a>
      </li>

      <li class="content-link content-link-js" data-page="multi-import-setup">
          <a href="{{action('ImportMultiFormController@index', ['pid'=>$pid])}}">Import MF Records Setup</a>
      </li>

      <li class="content-link content-link-js" data-page="scheme-import-setup">
        <a href="{{ action('FormController@importFormViewK2',['pid' => $pid]) }}">Kora 2 Scheme Importer</a>
      </li>

      <li class="content-link content-link-js">
        <a href="{{ action('ExportController@exportProject',['pid' => $pid]) }}">Export Project</a>
      </li>
    @endif
  </ul>
</div>
