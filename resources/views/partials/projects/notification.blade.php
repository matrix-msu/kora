<div class="notification dismiss @if($notification['warning']) warning @endif">
  <div class="container">
    <div class="note">
      <p class="ml-m">{{$notification['message']}}</p>
    </div>
    <div class="view-updates view updates-js hidden">
      <p>View Updates</p>
    </div>
    <div class="toggle-notification toggle-notification-js">
      <p>Dismiss</p>
    </div>
  </div>
</div>