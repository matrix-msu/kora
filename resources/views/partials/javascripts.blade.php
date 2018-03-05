@if(View::hasSection('javascripts'))
    @yield('javascripts')
@else
  <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
  <script type="text/javascript" src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>

  <!-- Google reCAPTCHA -->
  <script type="text/javascript" src="https://www.google.com/recaptcha/api.js" async defer></script>
  <!-- Files for select 2-->
  <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.0-rc.2/js/select2.min.js"></script>
@endif
