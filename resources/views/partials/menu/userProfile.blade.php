<li class="navigation-profile">
  <a href="#" class="profile-toggle navigation-toggle-js">
    <?php  $imgpath = 'profiles/' . \Auth::user()->id . '/' . \Auth::user()->profile ?>
    @if(File::exists( config('app.base_path') . '/public/app/' . $imgpath ))
      <img class="profile-picture" src="{{config('app.storage_url') . $imgpath}}">
    @else
      <i class="icon icon-user-little"></i>
    @endif
  </a>

  <ul class="navigation-sub-menu navigation-sub-menu-js">
    <li class="header">
      Hello, {{ Auth::user()->username }}!
    </li>
    <li class="link">
      <a href="{{ action('Auth\UserController@index',['uid'=>Auth::user()->id]) }}">View My Profile</a>
    </li>
    <li class="link">
      <a href="{{action('Auth\UserController@editProfile',['uid'=>Auth::user()->id])}}">Edit My Profile</a>
    </li>
    <li class="link">
      <a href="#">My Preferences</a>
    </li>
    <li class="link">
      <a href="#">My User Permissions</a>
    </li>
    <li class="link pre-spacer">
      <a href="#">My Record History</a>
    </li>
    @if(\Auth::user()->admin==1)
      <li class="spacer mt-0"></li>
      <li class="link first">
        <a href="{{ action('TokenController@index') }}">Token Management</a>
      </li>
      <li class="link">
        <a href="{{ action('AdminController@users') }}">User Management</a>
      </li>
      @if(\Auth::user()->id==1)
          <li class="link">
              <a href="{{ action('BackupController@index') }}">Backup Management</a>
          </li>
          <li class="link pre-spacer">
              <a href="{{ action('ExodusController@index') }}">Kora 2 Exodus</a>
          </li>
      @endif
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
