<nav class="navbar navbar-inverse navbar-fixed-top">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">{{trans('partials_install_nav.nav')}}</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="{{ url('/') }}">Kora 3</a>
        </div>
		
        <div id="navbar" class="collapse navbar-collapse">
          <ul class="nav navbar-nav">
            <!-- Left justified links -->
              @yield('leftNavLinks')
          </ul>
		  <ul class="nav navbar-nav navbar-right">
		    <!-- Right justified links -->
			@if (Auth::guest())
				<li><a href="{{ url('/install') }}">{{trans('partials_install_nav.install')}}</a></li>
			@else
				<li class="dropdown">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">{{ Auth::user()->username }} <span class="caret"></span></a>
					<ul class="dropdown-menu" role="menu">
						<li><a href="{{ url('/user') }}">{{trans('partials_install_nav.profile')}}</a></li>
						<li><a href="{{ url('/auth/logout') }}">{{trans('partials_install_nav.logout')}}</a></li>
					</ul>
				</li>
			@endif
		</ul>
        </div><!--/.nav-collapse -->
      </div>
</nav>