<li class="dropdown">
    <a href="#" class="dropdown-toggle" data-toggle="dropdown">{{trans('partials_menu_dashboard.dash')}}<b class="caret"></b></a>
    <ul class="dropdown-menu">
        <li><a href="{{ url('/projects') }}">{{trans('partials_nav.dashboard')}}</a></li>
        <li class="divider"></li>
        <li><a href="{{ url('/admin/users') }}">{{trans('partials_menu_dashboard.users')}}</a></li>
        <li><a href="{{ url('/tokens') }}">{{trans('partials_menu_dashboard.tokens')}}</a></li>
        @if(Auth::user()->id == 1)
        <li><a href="{{ url('/backup') }}">{{trans('partials_menu_dashboard.backups')}}</a></li>
        @endif
        <li><a href="{{ url('/install/config') }}">{{trans('partials_menu_dashboard.env')}}</a></li>
        <li><a href="{{ url('/plugins') }}">{{trans('partials_menu_dashboard.plugin')}}</a></li>
        <li><a href="{{ url('/update') }}">{{trans('partials_menu_dashboard.update')}}</a></li>
        <li><a href="{{ url('/exodus') }}">{{trans('partials_menu_dashboard.exodus')}}</a></li>
    </ul>
</li>