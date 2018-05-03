<div class="drawer-element drawer-element-js">
  <a href="#" class="drawer-toggle drawer-toggle-js" data-drawer="{{ $openDashboardDrawer or '0' }}">
    <i class="icon icon-dashboard"></i>
    <span>Dashboard</span>
    <i class="icon icon-chevron"></i>
  </a>
  <ul class="drawer-content drawer-content-js">
    <li class="content-link head">
      <a href="{{ url('/dashboard') }}">
        <span>Dashboard Home</span>
      </a>
    </li>

    <li class="content-link">
        <a href="#">Edit Dashboard</a>
    </li>

    <li class="content-link">
        <a href="#">Add Dashboard Block</a>
    </li>
  </ul>
</div>

<div class="drawer-element drawer-element-js">
  <a href="#" class="drawer-toggle drawer-toggle-js" data-drawer="{{ $openProjectDrawer or '0' }}">
    <i class="icon icon-projects"></i>
    <span> Projects</span>
    <i class="icon icon-chevron"></i>
  </a>
  <ul class="drawer-content drawer-content-js">
      <li class="content-link head">
        <a href="{{ url('/projects') }}">
          <span>Projects Home</span>
        </a>
      </li>

      @if(\Auth::user()->admin==1)
        <li class="content-link">
          <a href="{{ url('/projects/create') }}">Create New Project</a>
        </li>
        <li class="content-link">
          <a href="{{ url('/projects/import') }}">Import Project Setup</a>
        </li>
      @endif

      <?php $allowed_projects = \Auth::user()->allowedProjects() ?>
      @if(sizeof($allowed_projects) > 1)
        <li class="content-link" id="project-submenu">
          <a href='#' class="drawer-sub-menu-toggle drawer-sub-menu-toggle-js" data-toggle="dropdown">
            <span>Jump to Project</span>
            <i class="icon icon-plus"></i>
          </a>

          <ul class="drawer-deep-menu drawer-deep-menu-js">
            @foreach($allowed_projects as $project)
              <li class="drawer-deep-menu-link">
                <a href="{{ url('/projects/'.$project->pid) }}">{{ $project->name }}</a>
              </li>
            @endforeach
          </ul>
        </li>
      @endif
  </ul>
</div>
