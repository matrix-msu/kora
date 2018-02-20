@if(View::hasSection('javascripts'))
    @yield('javascripts')
@else
  <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
  <script type="text/javascript" src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>

  <!-- Google reCAPTCHA -->
  <script type="text/javascript" src="https://www.google.com/recaptcha/api.js" async defer></script>
  <!-- Files for select 2-->
  <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.0-rc.2/js/select2.min.js"></script>
  <!-- For Schedule -->
  <script src='{{ config('app.url') }}bower_components/fullcalendar/dist/fullcalendar.js'></script>
  <!-- For Geolocator -->
  <script src="{{ config('app.url') }}leaflet/leaflet.js"></script>
  <!-- For Documents -->
  <script src="{{ config('app.url') }}fileUpload/js/vendor/jquery.ui.widget.js"></script>
  <script src="{{ config('app.url') }}fileUpload/js/jquery.iframe-transport.js"></script>
  <script src="{{ config('app.url') }}fileUpload/js/jquery.fileupload.js"></script>
  <!-- For Gallery -->
  <script type="text/javascript" src="//code.jquery.com/jquery-migrate-1.2.1.min.js"></script>
  <script type="text/javascript" src="{{ config('app.url') }}slick/slick/slick.min.js"></script>
  <!-- For Playlist and Video -->
  <script type="text/javascript" src="{{ config('app.url') }}jplayer/jquery.jplayer.min.js"></script>
  <script type="text/javascript" src="{{ config('app.url') }}jplayer/jplayer.playlist.min.js"></script>
  <!-- For 3D Model -->
  <script type="text/javascript" src="{{ config('app.url') }}jsc3d/jsc3d.js"></script>
  <script type="text/javascript" src="{{ config('app.url') }}jsc3d/jsc3d.webgl.js"></script>
  <script type="text/javascript" src="{{ config('app.url') }}jsc3d/jsc3d.touch.js"></script>
@endif
