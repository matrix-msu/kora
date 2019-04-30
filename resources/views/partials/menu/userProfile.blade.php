<li class="navigation-profile">
  <a href="#" class="profile-toggle navigation-toggle-js">
    <?php  $imgpath = 'app/profiles/' . \Auth::user()->id . '/' . \Auth::user()->profile ?>
    @if(File::exists( public_path('app/' . $imgpath) ))
      <img class="profile-picture" src="{{url($imgpath)}}">
    @else
      <i class="icon icon-user-little"></i>
    @endif
  </a>

  <ul class="navigation-sub-menu navigation-sub-menu-js">
    <li class="header">
      Hello, {{ Auth::user()->first_name }}!
    </li>
    <li class="link">
      <a href="{{ action('Auth\UserController@index',['uid'=>Auth::user()->id]) }}">View My Profile</a>
    </li>
    <li class="link">
      <a href="{{action('Auth\UserController@editProfile',['uid'=>Auth::user()->id])}}">Edit My Profile</a>
    </li>
    <li class="link">
      <a href="{{action('Auth\UserController@preferences',['uid'=>Auth::user()->id])}}">My Preferences</a>
    </li>
    <li class="link">
      <a href="{{ action('Auth\UserController@index',['uid'=>Auth::user()->id, 'section' => 'permissions']) }}">My User Permissions</a>
    </li>
    <li class="link pre-spacer">
      <a href="{{ action('Auth\UserController@index',['uid'=>Auth::user()->id, 'section' => 'history']) }}">My Record History</a>
    </li>
    @if(\Auth::user()->admin==1)
      <li class="spacer mt-0"></li>
	  <li class="link first {{ \Auth::user()->id==1 ? '' : 'pre-spacer' }}">
        <a href="{{ action('AdminController@users') }}">User Management</a>
      </li>
	  <li class="link">
        <a href="{{ action('TokenController@index') }}">Token Management</a>
      </li>
    @endif
    <li class="spacer mt-0"></li>
    <li class="link">
        <form id="global_logout_link" class="form-horizontal" role="form" method="POST" action="{{ url('/logout') }}">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
              <a href="javascript: submitLogout()">
                <span class="left">Logout</span>
                <i class="icon icon-logout"></i>
              </a>
        </form>
        <script>
            function submitLogout() {
                $( "#global_logout_link" ).submit();
            }
        </script>
    </li>
  </ul>
</li>
