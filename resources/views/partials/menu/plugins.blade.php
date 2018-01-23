<li class="dropdown">
    <a href="#" class="dropdown-toggle" data-toggle="dropdown">{{trans('partials_menu_plugins.plugins')}}<b class="caret"></b></a>
    <ul class="dropdown-menu">
        @foreach(\Auth::user()->getActivePlugins() as $plugin)
            <li class="dropdown-submenu" id="plugin-submenu"> <a href="#" data-toggle="dropdown">{{$plugin->name}}</a>
                <ul class="dropdown-menu scrollable-submenu">
                    @foreach($plugin->menus() as $menu)
                        <li><a href="{{config('app.url').'plugins/'.$plugin->url.'/loadView/'.$menu->url}}">{{ $menu->name }}</a></li>
                    @endforeach
                </ul>
            </li>
        @endforeach
    </ul>
</li>