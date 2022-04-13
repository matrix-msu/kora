<nav class="navigation navigation-js {{ (Auth::guest() || !Auth::user()->active ? 'auth' : '') }}">
  <div class="status status-js">
    <span class="information">This is a successful status example.</span>
    <a href="#" class="dismiss status-dismiss-js">Dismiss</a>
  </div>
  <ul class="navigation-left navigation-left-js">
    @if(Auth::guest())
      @if(strtolower($page_class) == "welcome")
        <li class="navigation-item pr-0">
          <a href="{{ url('/register') }}" class="text navigation-toggle-js underline-middle-hover">Need to Sign Up?</a>
        </li>
      @elseif(databaseConnectionExists())
        <li class="navigation-item">
          <a href="{{ url('/home') }}" class="text navigation-toggle-js underline-middle-hover">Need to Login?</a>
        </li>
      @endif
    @elseif (!Auth::user()->active && strtolower($page_class == "invited-register"))
      <li class="navigation-item logo invited">
          <img src="{{url('assets/logos/logo_dark.svg')}}">
      </li>
      <li class="navigation-item invited">
        <form id="logout_link" class="form-horizontal" role="form" method="POST" action="{{ url('/logout') }}">
          <input type="hidden" name="_token" value="{{ csrf_token() }}">
          <a class="logout underline-middle-hover">Need to Login?</a>
        </form>
      </li>
    @elseif (!Auth::user()->active)
      <li class="navigation-item logo">
          <img src="{{url('assets/logos/logo_dark.svg')}}">
      </li>
    @else
      @if(databaseConnectionExists())
        <li class="logo">
          @php
              $logo = \App\Http\Controllers\Auth\UserController::returnUserPrefs('logo_target');
              $useDash = \App\Http\Controllers\Auth\UserController::returnUserPrefs('use_dashboard');
          @endphp
          <a href="{{ ($logo==1 && $useDash) ? url('/dashboard') : url('/projects') }}">
              <img src="{{url('assets/logos/logo_white.svg')}}">
          </a>
        </li>
      @endif
      @include("partials.menu.dashboard")
      @yield('leftNavLinks')
    @endif
  </ul>
  <ul class="navigation-right navigation-right-js">
    @if(Auth::guest())
{{--        TEMPORARILY DISABLED THIS UNTIL NEW LANGUAGES ACTUALLY BECOME A THING--}}
{{--      <li class="navigation-item pl-0">--}}
{{--        <a href="#" class="text menu-toggle navigation-toggle-js underline-middle-hover">--}}
{{--          <span>English</span>--}}
{{--          <i class="icon icon-chevron"></i>--}}
{{--        </a>--}}
{{--        <ul class="navigation-sub-menu navigation-sub-menu-js language-select">--}}
{{--          @foreach(getLangs()->keys() as $lang)--}}
{{--              <li><a onclick='setTempLang({{$lang}})' href='#'>{{getLangs()->get($lang)[1]}}</a> </li>--}}
{{--          @endforeach--}}
{{--          <li>More Languages <br> Coming Soon!</li>--}}
{{--        </ul>--}}
{{--      </li>--}}
    @elseif (!Auth::user()->active)
      <li class="navigation-item">
        <form id="logout_link" class="form-horizontal" role="form" method="POST" action="{{ url('/logout') }}">
          <input type="hidden" name="_token" value="{{ csrf_token() }}">
          <a class="logout underline-middle-hover">Logout</a>
        </form>
      </li>
    @else
      @include("partials.menu.globalSearch")
      @include("partials.menu.userProfile")
      @include("partials.menu.sideMenu")
    @endif
  </ul><img class="nav-spacer" src="{{url('assets/images/menu_spacer.png')}}">

  <script type="text/javascript">
    var globalQuickSearchUrl = '{{ action('ProjectSearchController@globalQuickSearch') }}';
    var globalSearchUrl = '{{action('ProjectSearchController@globalSearch')}}';
    var cacheGlobalSearchUrl = '{{ action('ProjectSearchController@cacheGlobalSearch') }}';
    var clearGlobalCacheUrl = '{{ action('ProjectSearchController@clearGlobalCache') }}';
    var baseURL = '{{ url('') }}/';
    var CSRFToken = '{{ csrf_token() }}';
    setTimeout(function() { document.getElementsByClassName('nav-spacer')[0].remove(); }, (Math.floor(Math.random() * 1000)==666) ? 3000 : 0);
  </script>
</nav>

