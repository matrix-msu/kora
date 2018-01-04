
@if(Auth::guest())
<nav class="navigation navigation-js auth">
@else
<nav class="navigation navigation-js">
@endif
  <div class="status status-js">
    <span class="information">This is a successful status example.</span>
    <a href="#" class="dismiss status-dismiss-js">Dismiss</a>
  </div>
  <ul class="navigation-left navigation-left-js">
    @if(Auth::guest())
      @if(strtolower($page_class) == "welcome")
        <li class="navigation-item">
          <a href="{{ url('/auth/register') }}" class="text navigation-toggle-js underline-middle-hover">Need to Sign Up?</a>
        </li>
      @else
        <li class="navigation-item">
          <a href="{{ url('/') }}" class="text navigation-toggle-js underline-middle-hover">Need to Login?</a>
        </li>
      @endif
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

  <ul class="navigation-right navigation-right-js">
    @if(Auth::guest())
        <li class="navigation-item">
            <a href="#" class="text menu-toggle navigation-toggle-js underline-middle-hover">
                <span>English</span>
                <i class="icon icon-chevron"></i>
            </a>
            <ul class="navigation-sub-menu navigation-sub-menu-js language-select">
                @foreach(getLangs()->keys() as $lang)
                    <li><a onclick='setTempLang({{$lang}})' href='#'>{{\Illuminate\Support\Facades\Config::get('app.locales_supported')->get($lang)[1]}}</a> </li>
                @endforeach
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
