<li class="kora_nav_item">
    <a href="#" class="kora_nav_item_title">Dashboard<img class="kora_nav_icon_post_text" src="{{ env('BASE_URL') }}images/menu_arrow.svg"></a>
    <ul class="kora_nav_sub_menu">
        <li class="kora_nav_sub_menu_item">
            <a href="{{ url('/dashboard') }}"><img src="{{ env('BASE_URL') }}images/menu_dash.svg">Dashboard</a>
        </li>
        <li class="kora_nav_sub_menu_spacer"></li>
        <li class="kora_nav_sub_menu_item">
            <a href="#">Edit Dashboard</a>
        </li>
        <li class="kora_nav_sub_menu_item">
            <a href="#">Add Dashboard Block</a>
        </li>
    </ul>
</li>
<li class="kora_nav_item">
    <a href="#" class="kora_nav_item_title">Projects<img class="kora_nav_icon_post_text" src="{{ env('BASE_URL') }}images/menu_arrow.svg"></a>
    <ul class="kora_nav_sub_menu">
        <li class="kora_nav_sub_menu_item">
            <a href="{{ url('/projects') }}">Projects</a>
        </li>
        @if(\Auth::user()->admin==1)
            <li class="kora_nav_sub_menu_spacer"></li>
            <li class="kora_nav_sub_menu_item">
                <a href="{{ url('/projects/create') }}">Create New Project</a>
            </li>
            <li class="kora_nav_sub_menu_item">
                <a href="{{ url('/projects/import') }}">Import Project Setup</a>
            </li>
        @endif
        <?php $allowed_projects = \Auth::user()->allowedProjects() ?>
        @if(sizeof($allowed_projects) > 1)
            <li class="kora_nav_sub_menu_item">
                <a href='#' class="kora_nav_sub_menu_item_title">Jump to Project<img class="kora_nav_icon_post_text" src="{{ env('BASE_URL') }}images/menu_plus.svg"></a>
                <ul class="kora_nav_deep_menu">
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