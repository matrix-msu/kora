<li class="navigation-item">
  <a href="#" class="menu-toggle navigation-toggle-js">
    <i class="icon icon-minus mr-sm"></i>
    <span>{{ \App\Http\Controllers\ProjectController::getProject($pid)->name }}</span>
    <i class="icon icon-chevron"></i>
  </a>
  <ul class="navigation-sub-menu navigation-sub-menu-js">
    <li class="link link-head">
      <a href="{{ url('/projects/'.$pid) }}">
        <i class="icon icon-project"></i>
        <span>Project Home</span>
      </a>
    </li>

    @if (\Auth::user()->admin ||  \Auth::user()->isProjectAdmin(\App\Http\Controllers\ProjectController::getProject($pid)))
      <li class="spacer full"></li>

      <li class="link first">
        <a href="{{action('FormController@create', ['pid'=>$pid])}}">Create New Form</a>
      </li>

      <li class="link">
        <a href="{{action('FormController@importFormView', ['pid'=>$pid])}}">Import Form Setup</a>
      </li>

      <?php $allowed_forms = \Auth::user()->allowedForms($pid) ?>
      @if(sizeof($allowed_forms) > 0 )
        <li class="link" id="project-submenu">
          <a href='#' class="navigation-sub-menu-toggle navigation-sub-menu-toggle-js" data-toggle="dropdown">
            <span>Jump to Form</span>
            <i class="icon sub-menu-icon icon-plus"></i>
          </a>

          <ul class="navigation-deep-menu navigation-deep-menu-js">
			
			<?php
			// Sort forms by name
			$name_fid_forms = [];
			
			foreach ($allowed_forms as $form)
			{
			  $name_fid_forms[$form->fid] = $form->name;
			}
			
			asort($name_fid_forms, SORT_NATURAL | SORT_FLAG_CASE);
			?>
		    
            @foreach($name_fid_forms as $form_fid => $form_name)
              <li class="deep-menu-item">
                <a href="{{ action('FormController@show', ['pid'=>$pid, 'fid' => $form_fid]) }}">{{ $form_name }}</a>
              </li>
            @endforeach
          </ul>
        </li>
      @endif

      <li class="link pre-spacer">
        <a href="{{ action('ProjectSearchController@keywordSearch', ['pid'=>$pid]) }}">Search Project Records</a>
      </li>

      <li class="spacer"></li>

      <li class="link first">
        <a href="{{ action('ProjectController@edit', ['pid'=>$pid]) }}">Edit Project Information</a>
      </li>

      <li class="link">
        <a href="{{ action('ProjectGroupController@index', ['pid'=>$pid]) }}">Project Permissions</a>
      </li>

      <li class="link">
        <a href="{{ action('OptionPresetController@index',['pid' => $pid]) }}">Field Value Presets</a>
      </li>

      <li class="link">
        <a href="{{ action('FormController@importFormViewK2',['pid' => $pid]) }}">Kora 2 Scheme Importer</a>
      </li>

      <li class="link">
        <a href="{{ action('ExportController@exportProject',['pid' => $pid]) }}">Export Project</a>
      </li>
    @endif
  </ul>
</li>
