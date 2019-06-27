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
      @elseif(isInstalled())
        <li class="navigation-item">
          <a href="{{ url('/') }}" class="text navigation-toggle-js underline-middle-hover">Need to Login?</a>
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
      @if(isInstalled())
        <li class="logo">
          @php $pref = 'logo_target' @endphp
          <a href="{{ \App\Http\Controllers\Auth\UserController::returnUserPrefs($pref) == "1" ? url('/dashboard') : url('/projects') }}">
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
      <li class="navigation-item pl-0">
        <a href="#" class="text menu-toggle navigation-toggle-js underline-middle-hover">
          <span>English</span>
          <i class="icon icon-chevron"></i>
        </a>
        <ul class="navigation-sub-menu navigation-sub-menu-js language-select">
          @foreach(getLangs()->keys() as $lang)
              <li><a onclick='setTempLang({{$lang}})' href='#'>{{getLangs()->get($lang)[1]}}</a> </li>
          @endforeach
          <li>More Languages <br> Coming Soon!</li>
        </ul>
      </li>
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
  </ul>

  <script type="text/javascript">
    var globalQuickSearchUrl = '{{ action('ProjectSearchController@globalQuickSearch') }}';
    var globalSearchUrl = '{{action('ProjectSearchController@globalSearch')}}';
    var cacheGlobalSearchUrl = '{{ action('ProjectSearchController@cacheGlobalSearch') }}';
    var clearGlobalCacheUrl = '{{ action('ProjectSearchController@clearGlobalCache') }}';
    var getProjectPermissionsModal = '{{ action('ProjectController@getProjectPermissionsModal') }}';
    var requestProjectPermissionsURL = '{{ action('ProjectController@request') }}';
    var baseURL = '{{ url('') }}/';
    var CSRFToken = '{{ csrf_token() }}';
  </script>
</nav>

