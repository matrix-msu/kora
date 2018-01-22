<!doctype html>

<html lang="en">
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, viewport-fit=cover">
        <title>Kora 3 - {{ $page_title }}</title>

        @if(isInstalled())
          @if(View::hasSection('stylesheets'))
              @yield('stylesheets')
          @else
            <!-- For Schedule -->
            <link rel="stylesheet" href="{{ env('BASE_URL') }}bower_components/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css" />
            <link rel='stylesheet' href="{{ env('BASE_URL') }}bower_components/fullcalendar/dist/fullcalendar.css" />
            <!-- For Geolocator -->
            <link rel="stylesheet" href="{{ env('BASE_URL') }}leaflet/leaflet.css" />
            <!-- For Documents -->
            <link rel="stylesheet" href="{{ env('BASE_URL') }}fileUpload/css/jquery.fileupload.css">
            <link rel="stylesheet" href="{{ env('BASE_URL') }}fileUpload/css/jquery.fileupload-ui.css">
            <!-- For Gallery -->
            <link rel="stylesheet" type="text/css" href="{{ env('BASE_URL') }}slick/slick/slick.css"/>
            <link rel="stylesheet" type="text/css" href="{{ env('BASE_URL') }}slick/slick/slick-theme.css"/>
            <!-- For Playlist and Video -->
            <link rel="stylesheet" type="text/css" href="{{ env('BASE_URL') }}jplayer/pink.flag/css/jplayer.pink.flag.min.css"/>
            <!-- For 3D Model -->
            <script type="text/javascript" src="{{ env('BASE_URL') }}jsc3d/jsc3d.js"></script>
            <script type="text/javascript" src="{{ env('BASE_URL') }}jsc3d/jsc3d.webgl.js"></script>
            <script type="text/javascript" src="{{ env('BASE_URL') }}jsc3d/jsc3d.touch.js"></script>
          @endif
        @endif

        <link rel="stylesheet" href="{{env('BASE_URL')}}assets/css/app.css">
    </head>
    <body class="{{ str_hyphenated($page_class) }}-body @if(Auth::guest() && isInstalled()) auth-body @endif">
      @include('partials.nav')

      <div class="side-menu side-menu-js">
        <div class="blanket blanket-js"></div>
        <aside class="content">
        </aside>
      </div>


      <div class="{{ str_hyphenated($page_class) }} @if(Auth::guest() && isInstalled()) auth @endif">
        @yield('header')
        @yield('body')
        @yield('footer')

        @if(Auth::guest() && isInstalled())
          @include('partials.footer')
        @endif
      </div>

      @if(Auth::guest())
        @include('partials.projects.javascripts')

        <script>
          function setTempLang(selected_lang){
            var langURL ="{{action('WelcomeController@setTemporaryLanguage')}}";
            console.log("Language change started: "+langURL);
            $.ajax({
              url:langURL,
              method:'POST',
              data: {
                "_token": "{{ csrf_token() }}",
                "templanguage": selected_lang
              },
              success: function(data){
                console.log(data);
                location.reload();
              }
            });
          }
        </script>
      @endif

      @include('partials.javascripts')
    </body>
</html>
