<li class="navigation-item">
    <a href="#" class="menu-toggle navigation-toggle-js">
      <span>{{ \App\Http\Controllers\ProjectController::getProject($pid)->name }}</span>
      <i class="icon icon-chevron"></i>
    </a>
    <ul class="navigation-sub-menu navigation-sub-menu-js">
        <li><a href="{{ url('/projects/'.$pid) }}">{{trans('partials_menu_project.home')}}</a></li>
        <?php $allowed_projects = \Auth::user()->allowedProjects() ?>
        @if(sizeof($allowed_projects) > 1)
            <li class="link" id="project-submenu">
              <a href='#' class="navigation-sub-menu-toggle-js" data-toggle="dropdown">{{trans('partials_menu_project.jump')}}</a>
                <ul class="navigation-deep-menu navigation-deep-menu-js">
                    @foreach($allowed_projects as $project)
                        @if($project->pid != $pid)
                            <li class="link"><a href="{{ url('/projects/'.$project->pid) }}">{{ $project->name }}</a></li>
                        @endif
                    @endforeach
                </ul>
            </li>
        @endif
        @if (\Auth::user()->admin ||  \Auth::user()->isProjectAdmin(\App\Http\Controllers\ProjectController::getProject($pid)))
            <li class="spacer"></li>
            <li class="link">
              <a href="{{action('ProjectGroupController@index', ['pid'=>$pid])}}">{{trans('partials_menu_project.groups')}}</a>
            </li>
            <li class="link">
              <a href="{{action('OptionPresetController@index', ['pid'=>$pid])}}">{{trans('partials_menu_project.presets')}}</a>
            </li>
            <li class="link">
              <a href="{{action('FormController@importFormViewK2',['pid' => $pid])}}">{{trans('partials_menu_project.k2import')}}</a>
            </li>
        @endif
    </ul>
</li>
