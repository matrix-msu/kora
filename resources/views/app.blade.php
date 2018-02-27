<!doctype html>

<html lang="en">
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, viewport-fit=cover">
        <title>Kora 3 - {{ $page_title }}</title>

        @if(isInstalled())
          @if(View::hasSection('stylesheets'))
              @yield('stylesheets')
          @else
            <!-- For Geolocator -->
            <link rel="stylesheet" href="{{ config('app.url') }}leaflet/leaflet.css" />
            <!-- For Documents -->
            <link rel="stylesheet" href="{{ config('app.url') }}fileUpload/css/jquery.fileupload.css">
            <link rel="stylesheet" href="{{ config('app.url') }}fileUpload/css/jquery.fileupload-ui.css">
            <!-- For Gallery -->

            <!-- For Playlist and Video -->
            <link rel="stylesheet" type="text/css" href="{{ config('app.url') }}jplayer/pink.flag/css/jplayer.pink.flag.min.css"/>
            <!-- For 3D Model -->
            <script type="text/javascript" src="{{ config('app.url') }}jsc3d/jsc3d.js"></script>
            <script type="text/javascript" src="{{ config('app.url') }}jsc3d/jsc3d.webgl.js"></script>
            <script type="text/javascript" src="{{ config('app.url') }}jsc3d/jsc3d.touch.js"></script>
          @endif
        @endif

        <link rel="stylesheet" href="{{config('app.url')}}assets/css/app.css">
    </head>
    <body class="{{ str_hyphenated($page_class) }}-body @if((Auth::guest() || !Auth::user()->active) && isInstalled()) auth-body @endif">
      @include('partials.nav')

      <div class="side-menu side-menu-js">
        <div class="blanket blanket-js"></div>
        <aside class="content">
        </aside>
      </div>


      <div class="{{ str_hyphenated($page_class) }} @if((Auth::guest() || !Auth::user()->active) && isInstalled()) auth @endif">
        @yield('header')
        @yield('body')
        @yield('footer')

        @if((Auth::guest() || !Auth::user()->active) && isInstalled())
          @include('partials.footer')
        @endif
      </div>

      @if(Auth::guest() || !Auth::user()->active)
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
