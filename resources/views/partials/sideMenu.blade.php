<?php
  $sidebarCookie = false;
  if (isset($_COOKIE['sidebar']) && $_COOKIE['sidebar'] == "1") { $sidebarCookie = true; }
?>

@php $pref = 'keep_sidemenu' @endphp
<div class="hidden keep-sidemenu {{ \App\Http\Controllers\Auth\UserController::returnUserPrefs($pref) == "1" ? 'true' : '' }}"></div>
<div class="side-menu side-menu-js <?php if ($sidebarCookie) { echo 'active'; } ?>">
  <div class="blanket blanket-js"></div>
  <aside class="aside-content">
    <div class="header-elements">
      @yield('aside-content')
    </div>
    <div class="footer-elements">
      <div class="drawer-element">
        <a target="_blank" href="https://github.com/matrix-msu/Kora3/issues" class="drawer-toggle">
          <i class="icon icon-feedback"></i>
          <span>Submit Feedback</span>
          <i class="icon icon-external-link"></i>
        </a>
      </div>

      <div class="drawer-element">
        <a target="_blank" href="https://github.com/matrix-msu/Kora3" class="drawer-toggle">
          <i class="icon icon-help"></i>
          <span>Help & Documentation</span>
          <i class="icon icon-external-link"></i>
        </a>
      </div>

      @if (null !== \Auth::user() && \Auth::user()->admin)
        @include('partials.sideMenu.management', ['openDrawer' => (isset($openManagement) && $openManagement)])

        @if (sizeof(\Auth::user()->getActivePlugins()) > 0)
          @include('partials.sideMenu.plugins')
        @endif
      @endif
    <div>
  </aside>
</div>
