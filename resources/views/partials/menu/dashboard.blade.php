<li class="navigation-item">
    <a href="#" class="kora_nav_item_title">Dashboard<img class="icon arrow-icon" src="{{ env('BASE_URL') }}assets/images/menu_arrow.svg"></a>
    <ul class="navigation-sub-menu navigation-sub-menu-js">
        <li class="link head">
            <a href="{{ url('/dashboard') }}">
              <img src="{{ env('BASE_URL') }}assets/images/menu_dash.svg">
              <span>Dashboard</span>
            </a>
        </li>
        <li class="spacer"></li>
        <li class="link">
            <a href="#">Edit Dashboard</a>
        </li>
        <li class="link">
            <a href="#">Add Dashboard Block</a>
        </li>
    </ul>
</li>
<li class="navigation-item">
    <a href="#" class="kora_nav_item_title">Projects<img class="icon arrow-icon" src="{{ env('BASE_URL') }}assets/images/menu_arrow.svg"></a>
    <ul class="navigation-sub-menu navigation-sub-menu-js">
        <li class="link head">
            <a href="{{ url('/projects') }}">
              <img src="{{ env('BASE_URL') }}assets/images/menu_proj.svg">
              <span>Projects</span>
            </a>
        </li>
        @if(\Auth::user()->admin==1)
            <li class="spacer"></li>
            <li class="link">
                <a href="{{ url('/projects/create') }}">Create New Project</a>
            </li>
            <li class="link">
                <a href="{{ url('/projects/import') }}">Import Project Setup</a>
            </li>
        @endif
        <?php $allowed_projects = \Auth::user()->allowedProjects() ?>
        @if(sizeof($allowed_projects) > 1)
            <li class="link">
                <a href='#' class="kora_nav_sub_menu_item_title">Jump to Project<img class="icon" src="{{ env('BASE_URL') }}assets/images/menu_plus.svg"></a>
                <ul class="navigation-deep-menu navigation-deep-menu-js">
                    @foreach($allowed_projects as $project)
                        <li class="kora_nav_deep_menu_item">
                            <a href="{{ url('/projects/'.$project->pid) }}">{{ $project->name }}</a>
                        </li>
                    @endforeach
                </ul>
            </li>
        @endif
    </ul>
</li>
