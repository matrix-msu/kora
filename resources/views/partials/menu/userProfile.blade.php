<li class="navigation-profile">
  <a href="#" class="profile-toggle navigation-toggle-js">
    <?php  $imgpath = 'profiles/' . \Auth::user()->id . '/' . \Auth::user()->profile ?>
    @if(File::exists( env('BASE_PATH') . '/public/app/' . $imgpath ))
      <img class="profile-picture" src="{{env('STORAGE_URL') . $imgpath}}">
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
    <li class="spacer"></li>
    <li class="link">
      <a href="{{ url('/auth/logout') }}">
        <span class="left">Logout</span>
        <img class="logout-icon right" src="{{ env('BASE_URL') }}assets/images/menu_logout.svg">
      </a>
    </li>
  </ul>
</li>
