<style scoped>
    @media (max-width: 992px) {
        .navbar-header {
            float: none;
        }
        .navbar-toggle {
            display: block;
        }
        .navbar-collapse {
            border-top: 1px solid transparent;
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.1);
        }
        .navbar-collapse.collapse {
            display: none!important;
        }
        .navbar-nav {
            float: none!important;
            margin: 7.5px -15px;
        }
        .navbar-nav>li {
            float: none;
        }
        .navbar-nav>li>a {
            padding-top: 10px;
            padding-bottom: 10px;
        }
        .navbar-text {
            float: none;
            margin: 15px 0;
        }
        /* since 3.1.0 */
        .navbar-collapse.collapse.in {
            display: block!important;
        }
        .collapsing {
            overflow: hidden!important;
        }
    }
</style>

<nav class="navbar navbar-inverse navbar-fixed-top" role="navigation" style="background-image: inherit">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="{{ url('/') }}"><img style="height: 35px;width: 35px;margin-top: -7.5px" src="{{ env('BASE_URL') }}public/logos/KoraIII-Logo.gif"></a>
        </div>
		
        <div id="navbar" class="collapse navbar-collapse">
          <ul class="nav navbar-nav">
            <!-- Left justified links -->
              @include('partials.menu.dashboard')

              <style scoped>
                  .scrollable-submenu {
                      height: auto;
                      max-height: 300px;
                      overflow-x: hidden;
                  }
              </style>

              @yield('leftNavLinks')

              @if(\Auth::user() != null && sizeof(\Auth::user()->getActivePlugins()) > 0 )
              @include('partials.menu.plugins')
              @endif
          </ul>

		  <ul class="nav navbar-nav navbar-right">
		    <!-- Right justified links -->
			@if (Auth::guest())
				<li><a href="{{ url('/auth/register') }}">{{trans('partials_nav.register')}}</a></li>
                <li><a href="{{ action('Auth\UserController@activateshow') }}">{{trans('partials_nav.activation')}}</a></li>
			@else
                  <li class="dropdown">
                      <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">{{ Auth::user()->username }} <span class="caret"></span></a>
                      <ul class="dropdown-menu" role="menu">
                          <li><a href="{{ url('/user') }}">{{trans('partials_nav.viewprofile')}}</a></li>
                          <li><a href="{{ url('/auth/logout') }}">{{trans('partials_nav.logout')}}</a></li>
                      </ul>
                  </li>
              @if (!\Auth::user()->active)
                  <li><a href="{{ action('Auth\UserController@activateshow') }}">{{trans('partials_nav.activation')}}</a></li>
              @endif
			@endif
		</ul>
            <style scoped>
                #global_search {
                    width: 200px
                }

                @media (max-width: 992px) {
                    #global_search {
                        width: 100%;
                        margin: 0 0.5em 0 0.5em;
                    }

                    #global_search > .input-group {
                        width: 100%;
                    }
                }

                .spin {
                    -webkit-animation: spin 1s infinite linear;
                    -moz-animation: spin 1s infinite linear;
                    -o-animation: spin 1s infinite linear;
                    animation: spin 1s infinite linear;
                    -webkit-transform-origin: 50% 38%;
                    transform-origin:50% 38%;
                    -ms-transform-origin:50% 38%; /* IE 9 */
                }
                @-moz-keyframes spin {
                    from {
                        -moz-transform: rotate(0deg);
                    }
                    to {
                        -moz-transform: rotate(360deg);
                    }
                }

                @-webkit-keyframes spin {
                    from {
                        -webkit-transform: rotate(0deg);
                    }
                    to {
                        -webkit-transform: rotate(360deg);
                    }
                }

                @keyframes spin {
                    from {
                        transform: rotate(0deg);
                    }
                    to {
                        transform: rotate(360deg);
                    }
                }

            </style>

            @if ( ! is_null(\Auth::user()))
                <ul class="nav navbar-nav navbar-right" id="navbar_container">
                    <form class="navbar-form" role="search" id="global_search" action="{{action("ProjectSearchController@globalSearch")}}">
                        <div class="input-group">
                            <input type="text" class="form-control" placeholder="Global Search" name="query">
                            <div class="input-group-btn">
                                <button class="btn btn-default" id="global_submit" type="submit"><i class="glyphicon glyphicon-search" style="height: 1.4em; vertical-align: middle"></i></button>
                                <button style="display:none" id="global_loading" class="btn btn-default" disabled><i class="glyphicon glyphicon-refresh spin" style="height: 1.4em; vertical-align: middle"></i></button>
                            </div>
                        </div>
                    </form>
                </ul>
            @endif

            <script> $("#global_search").submit( function() {$("#global_submit").hide(); $("#global_loading").show(); } );</script>

        </div><!--/.nav-collapse -->
</nav>
</div>