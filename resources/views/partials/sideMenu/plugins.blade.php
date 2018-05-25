<div class="drawer-element drawer-element-js">
    <a href="#" class="drawer-toggle drawer-toggle-js">
        <i class="icon icon-plugins"></i>
        <span>Plugins</span>
        <i class="icon icon-chevron"></i>
    </a>

    <ul class="drawer-content drawer-content-js">
      @foreach(\Auth::user()->getActivePlugins() as $plugin)
        <li class="content-link">
          <a href="#" class="drawer-sub-menu-toggle drawer-sub-menu-toggle-js" data-toggle="dropdown">
            <span>{{ $plugin->name }}</span>
            <i class="icon icon-plus"></i>
          </a>

          <ul class="drawer-deep-menu drawer-deep-menu-js">
            @foreach($plugin->menus() as $menu)
              <li class="drawer-deep-menu-link">
                <a class="padding-fix" href="{{ config('app.url').'plugins/'.$plugin->url.'/loadView/'.$menu->url }}">
                  <span>{{ $menu->name }}</span>
                </a>
              </li>
            @endforeach
          </ul>
        </li>
      @endforeach
    </ul>
</div>
