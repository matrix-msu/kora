<div class="drawer-element drawer-element-js">
    <a href="#" class="drawer-toggle drawer-toggle-js" data-drawer="{{ $openDrawer ?? '0' }}">
        <i class="icon icon-gear"></i>
        <span>Management</span>
        <i class="icon icon-chevron"></i>
    </a>

    <ul class="drawer-content drawer-content-js">
        <li class="content-link content-link-js" data-page="admin-users">
            <a href="{{ action('AdminController@users') }}">
                <span>User Management</span>
            </a>
        </li>

        <li class="content-link content-link-js" data-page="tokens">
            <a href="{{ action('TokenController@index') }}">Token Management</a>
        </li>

        <li class="content-link content-link-js reverse-cache-open-js" data-page="reverse-cache">
            <a href="#">Build Reverse Association Cache</a>
        </li>

        <li class="content-link content-link-js" data-page="edit-config">
            <a href="{{ action('InstallController@editEnvConfigs') }}">Kora Configuration File</a>
        </li>

        @if(null !== \Auth::user() && \Auth::user()->id == 1)
            <li class="content-link content-link-js" data-page="update">
                <a href="{{ action('UpdateController@index') }}">Update Kora Installation</a>
            </li>
        @endif
    </ul>
</div>
