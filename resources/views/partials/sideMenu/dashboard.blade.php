@php $pref = 'use_dashboard' @endphp
@if (\App\Http\Controllers\Auth\UserController::returnUserPrefs($pref) == "1")
<div class="drawer-element drawer-element-js">
  <a href="{{ url('/dashboard') }}" class="drawer-toggle" data-drawer="{{ $openDashboardDrawer ?? '0' }}">
    <i class="icon icon-dashboard"></i>
    <span>Dashboard</span>
  </a>
</div>
@endif

<div class="drawer-element drawer-element-js">
  <a href="#" class="drawer-toggle drawer-toggle-js" data-drawer="{{ $openProjectDrawer ?? '0' }}">
    <i class="icon icon-projects"></i>
    <span> Projects</span>
    <i class="icon icon-chevron"></i>
  </a>
  <ul class="drawer-content drawer-content-js">
      <li class="content-link content-link-js" data-page="projects">
        <a href="{{ url('/projects') }}">
          <span>Projects Home</span>
        </a>
      </li>

      @if(!is_null(\Auth::user()) && \Auth::user()->admin==1)
        <li class="content-link content-link-js" data-page="project-create">
          <a href="{{ url('/projects/create') }}">Create New Project</a>
        </li>
        <li class="content-link content-link-js" data-page="project-import-setup">
          <a href="{{ url('/projects/import') }}">Import Project Setup</a>
        </li>
      @endif

      @php $allowed_projects = !is_null(\Auth::user()) ? \Auth::user()->allowedProjects() : array(); @endphp
      @if(sizeof($allowed_projects) > 1)
        <li class="content-link content-link-js" id="project-submenu">
          <a href='#' class="drawer-sub-menu-toggle drawer-sub-menu-toggle-js" data-toggle="dropdown">
            <span>Jump to Project</span>
            <i class="icon icon-plus"></i>
          </a>
		  
		  @php
            // Sort projects by name
            $name_pid_projects = [];

            foreach ($allowed_projects as $project) {
              $name_pid_projects[$project->id] = $project->name;
            }

            asort($name_pid_projects, SORT_NATURAL | SORT_FLAG_CASE);
		  @endphp

          <ul class="drawer-deep-menu drawer-deep-menu-js">
            @foreach($name_pid_projects as $project_pid => $project_name)
              <li class="drawer-deep-menu-link">
                <a href="{{ url('/projects/'.$project_pid) }}">{{ $project_name }}</a>
              </li>
            @endforeach
          </ul>
        </li>
      @endif
	  
	  @if(!is_null(\Auth::user()) && \Auth::user()->admin==0)
	  <li class="content-link request-permissions-js">
        <a class="project-request-perms-js" href="#">Request Project Permissions</a>
      </li>
	  @endif
  </ul>
</div>
