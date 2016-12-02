<li class="dropdown">
    <a href="#" class="dropdown-toggle" data-toggle="dropdown">{{ \App\Http\Controllers\ProjectController::getProject($pid)->name }}<b class="caret"></b></a>
    <ul class="dropdown-menu">
        <li><a href="{{ url('/projects/'.$pid) }}">{{trans('partials_menu_project.home')}}</a></li>
        <?php $allowed_projects = \Auth::user()->allowedProjects() ?>
        @if(sizeof($allowed_projects) > 1)
            <li class="dropdown-submenu" id="project-submenu"> <a href='#' data-toggle="dropdown">{{trans('partials_menu_project.jump')}}</a>
                <ul class="dropdown-menu scrollable-submenu">
                    @foreach($allowed_projects as $project)
                        @if($project->pid != $pid)
                            <li><a href="{{ url('/projects/'.$project->pid) }}">{{ $project->name }}</a></li>
                        @endif
                    @endforeach
                </ul>
            </li>
        @endif
        @if (\Auth::user()->admin ||  \Auth::user()->isProjectAdmin(\App\Http\Controllers\ProjectController::getProject($pid)))
            <li class="divider"></li>
            <li><a href="{{action('ProjectGroupController@index', ['pid'=>$pid])}}">{{trans('partials_menu_project.groups')}}</a></li>
            <li><a href="{{action('OptionPresetController@index', ['pid'=>$pid])}}">{{trans('partials_menu_project.presets')}}</a></li>
            <li><a href="{{action('FormController@importFormViewK2',['pid' => $pid])}}">{{trans('partials_menu_project.k2import')}}</a></li>
        @endif
    </ul>
</li>