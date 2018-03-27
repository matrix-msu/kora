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
      <a href="{{ url('/user') }}">View My Profile</a>
    </li>
    <li class="link">
      <a href="#">Edit My Profile</a>
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
        <a href="{{ url('/tokens') }}">Token Management</a>
      </li>
      <li class="link pre-spacer">
        <a href="{{ url('/admin/users') }}">User Management</a>
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
