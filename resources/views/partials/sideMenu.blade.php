<?php
  $sidebarCookie = false;
  if (isset($_COOKIE['sidebar']) && $_COOKIE['sidebar'] == "1") { $sidebarCookie = true; }
?>

<div class="side-menu side-menu-js <?php if ($sidebarCookie) { echo 'active'; } ?>">
  <div class="blanket blanket-js"></div>
  <aside class="aside-content">
    <div class="header-elements">
      @yield('aside-content')
    </div>
    <div class="footer-elements">
      <div class="drawer-element">
        <a target="_blank" href="https://github.com/matrix-msu/Kora3" class="drawer-toggle">
          <i class="icon icon-help"></i>
          <span>Help & Documentation</span>
          <i class="icon icon-external-link"></i>
        </a>
      </div>

      @include('partials.sideMenu.management')
      @include('partials.sideMenu.plugins')
    <div>
  </aside>
</div>
