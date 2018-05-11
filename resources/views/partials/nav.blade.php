
@if(Auth::guest() || !Auth::user()->active)
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
        <li class="navigation-item pr-0">
          <a href="{{ url('/register') }}" class="text navigation-toggle-js underline-middle-hover">Need to Sign Up?</a>
        </li>
      @elseif(isInstalled())
        <li class="navigation-item">
          <a href="{{ url('/') }}" class="text navigation-toggle-js underline-middle-hover">Need to Login?</a>
        </li>
      @endif
    @elseif (!Auth::user()->active)
      <li class="navigation-item logo">
          <i class="icon icon-placeholder"></i>
      </li>
    @else
      @if(isInstalled())
        <li class="logo">
          <a href="{{ url('/') }}" class="navigation-toggle-js">
            <i class="icon icon-placeholder"></i>
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
                    <li><a onclick='setTempLang({{$lang}})' href='#'>{{\Illuminate\Support\Facades\Config::get('app.locales_supported')->get($lang)[1]}}</a> </li>
                @endforeach
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
  <!--<script src="/assets/javascripts/navigation/breadcrumbs.js"></script>-->
  <script type="text/javascript">
    function collision($div1, $div2) {
      var x1 = $div1.offset().left;
      var y1 = $div1.offset().top;
      var h1 = $div1.outerHeight(true);
      var w1 = $div1.outerWidth(true);
      var b1 = y1 + h1;
      var r1 = x1 + w1;
      var x2 = $div2.offset().left;
      var y2 = $div2.offset().top;
      var h2 = $div2.outerHeight(true);
      var w2 = $div2.outerWidth(true);
      var b2 = y2 + h2;
      var r2 = x2 + w2;

      if (b1 < y2 || y1 > b2 || r1 < x2 || x1 > r2) return false;
      return true;
    }
    
    var result
    window.setInterval(function() {
      result = collision($('.navigation-left .navigation-item:last-child'), $('.navigation-search'));
      console.log('' + result)
      if (result === true) {
        $('.navigation-left').addClass('collapsed');
      }
      unsetBreadCrumbs ()
    }, 200);
    
    function unsetBreadCrumbs () {
      if (window.innerWidth >= 700 && $('.navigation-left').hasClass('collapsed')) {
        $('.collapsed').removeClass('collapsed');
      }
    }
    
  </script>
  
  <script type="text/javascript">
    var globalQuickSearchUrl = '{{ action('ProjectSearchController@globalQuickSearch') }}';
    var globalSearchUrl = '{{action('ProjectSearchController@globalSearch')}}';
    var cacheGlobalSearchUrl = '{{ action('ProjectSearchController@cacheGlobalSearch') }}';
    var clearGlobalCacheUrl = '{{ action('ProjectSearchController@clearGlobalCache') }}';
    var CSRFToken = '{{ csrf_token() }}';
  </script>
</nav>
