<nav class="navigation navigation-js">
  <ul class="navigation-left navigation-left-js">
    @if(Auth::guest())
      <li class="navigation-item">
        <a href="{{ url('/auth/register') }}" class="navigation-toggle-js">Register</a>
      </li>
    @else
      <li class="logo">
        <a href="{{ url('/') }}" class="navigation-toggle-js">
          <i class="icon icon-placeholder"></i>
        </a>
      </li>
      @include("partials.menu.dashboard")
      @yield('leftNavLinks')
    @endif
  </ul>

  <ul class="navigation-right">
    @if(Auth::guest())
      <li class="navigation-item">
        <a href="#" class="navigation-toggle-js">Current Language</a>
        <ul class="navigation-sub-menu navigation-sub-menu-js">
          <li class="link">Current Language</li>
          <li class="link">Another Language</li>
          <li class="link">Another Language</li>
        </ul>
      </li>

    @else
      @include("partials.menu.globalSearch")
      @include("partials.menu.userProfile")
      @include("partials.menu.sideMenu")
    @endif
  </ul>

  <script type="text/javascript">
    var globalQuickSearchUrl = '{{ action('ProjectSearchController@globalQuickSearch') }}';
    var globalSearchUrl = '{{action('ProjectSearchController@globalSearch')}}';
    var cacheGlobalSearchUrl = '{{ action('ProjectSearchController@cacheGlobalSearch') }}';
    var clearGlobalCacheUrl = '{{ action('ProjectSearchController@clearGlobalCache') }}';
    var CSRFToken = '{{ csrf_token() }}';
  </script>
</nav>
