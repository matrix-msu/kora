<!doctype html>

<html lang="en">
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
        <title>Kora 3 - {{ $page_title }}</title>

        @if(!isset($not_installed))
          @if(View::hasSection('stylesheets'))
              @yield('stylesheets')
          @else
            <!-- For Schedule -->
            <link rel="stylesheet" href="{{ env('BASE_URL') }}bower_components/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css" />
            <link rel='stylesheet' href='{{ env('BASE_URL') }}bower_components/fullcalendar/dist/fullcalendar.css' />
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
          @endif
        @endif

        <link rel="stylesheet" href="{{env('BASE_URL')}}/assets/css/app.css">
    </head>
    <body class="{{ str_hyphenated($page_title) }}-body">
      @include('partials.nav')

      <div class="side-menu side-menu-js">
        <div class="blanket blanket-js"></div>
        <aside class="content">
        </aside>
      </div>

      <div class="{{ str_hyphenated($page_title) }}">
        @yield('header')
        @yield('body')
        @yield('footer')
      </div>


      @include('partials.javascripts')
    </body>
</html>
