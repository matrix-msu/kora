<li class="dropdown">
    <a href="#" class="dropdown-toggle" data-toggle="dropdown">Dashboard<b class="caret"></b></a>
    <ul class="dropdown-menu">
        <li><a href="{{ url('/projects') }}">{{trans('nav.dashboard')}}</a></li>
        <li class="divider"></li>
        <li><a href="{{ url('/admin/users') }}">Manage Users</a></li>
        <li><a href="{{ url('/tokens') }}">Manage Tokens</a></li>
        @if(Auth::user()->id == 1)
        <li><a href="{{ url('/backup') }}">Manage Backups</a></li>
        @endif
        <li><a href="{{ url('/install/config') }}">Manage ENV File</a></li>
    </ul>
</li>