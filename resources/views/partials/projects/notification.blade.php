<div class="notification dismiss @if($notification['warning']) warning @endif @if($notification['static']) static-js @endif">
  <div class="container">
    <div class="note">
      <p class="ml-m mr-m">{{ $notification['message'] }}</p>
      <span class="">{{ $notification['description'] }}</span>
    </div>
    <div class="view-updates view-updates-js hidden">
      <p>View Updates</p>
    </div>
    <div class="toggle-notification toggle-notification-js">
      <p>Dismiss</p>
    </div>
  </div>
</div>