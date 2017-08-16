<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
        <link rel="stylesheet" href="{{env('BASE_URL')}}/assets/css/app.css">
        <!-- Latest compiled and minified JavaScript -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
        <!-- Google reCAPTCHA -->
        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
        <!-- Files for select 2-->
        <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.0-rc.2/css/select2.min.css" rel="stylesheet" />
        <script src="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.0-rc.2/js/select2.min.js"></script>
        @if(!isset($not_installed))
            <!-- For Rich Text -->
            <script src="{{ env('BASE_URL') }}ckeditor/ckeditor.js"></script>
            <!-- For Schedule -->
            <script type="text/javascript" src="{{ env('BASE_URL') }}bower_components/moment/min/moment.min.js"></script>
            <script type="text/javascript" src="{{ env('BASE_URL') }}bower_components/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js"></script>
            <link rel="stylesheet" href="{{ env('BASE_URL') }}bower_components/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css" />
            <link rel='stylesheet' href='{{ env('BASE_URL') }}bower_components/fullcalendar/dist/fullcalendar.css' />
            <script src='{{ env('BASE_URL') }}bower_components/fullcalendar/dist/fullcalendar.js'></script>
            <!-- For Geolocator -->
            <link rel="stylesheet" href="{{ env('BASE_URL') }}leaflet/leaflet.css" />
            <script src="{{ env('BASE_URL') }}leaflet/leaflet.js"></script>
            <!-- For Documents -->
            <link rel="stylesheet" href="{{ env('BASE_URL') }}fileUpload/css/jquery.fileupload.css">
            <link rel="stylesheet" href="{{ env('BASE_URL') }}fileUpload/css/jquery.fileupload-ui.css">
            <script src="{{ env('BASE_URL') }}fileUpload/js/vendor/jquery.ui.widget.js"></script>
            <script src="{{ env('BASE_URL') }}fileUpload/js/jquery.iframe-transport.js"></script>
            <script src="{{ env('BASE_URL') }}fileUpload/js/jquery.fileupload.js"></script>
            <!-- For Gallery -->
            <link rel="stylesheet" type="text/css" href="{{ env('BASE_URL') }}slick/slick/slick.css"/>
            <link rel="stylesheet" type="text/css" href="{{ env('BASE_URL') }}slick/slick/slick-theme.css"/>
            <script type="text/javascript" src="//code.jquery.com/jquery-migrate-1.2.1.min.js"></script>
            <script type="text/javascript" src="{{ env('BASE_URL') }}slick/slick/slick.min.js"></script>
            <!-- For Playlist and Video -->
            <link rel="stylesheet" type="text/css" href="{{ env('BASE_URL') }}jplayer/pink.flag/css/jplayer.pink.flag.min.css"/>
            <script type="text/javascript" src="{{ env('BASE_URL') }}jplayer/jquery.jplayer.min.js"></script>
            <script type="text/javascript" src="{{ env('BASE_URL') }}jplayer/jplayer.playlist.min.js"></script>
            <!-- For 3D Model -->
            <script type="text/javascript" src="{{ env('BASE_URL') }}jsc3d/jsc3d.js"></script>
            <script type="text/javascript" src="{{ env('BASE_URL') }}jsc3d/jsc3d.webgl.js"></script>
            <script type="text/javascript" src="{{ env('BASE_URL') }}jsc3d/jsc3d.touch.js"></script>
        @endif
        <title>Kora 3 - {{ $page_title }}</title>
    </head>
    <body>
        <div>
            @include('partials.nav')
        </div>

        <section class="{{ str_hyphenated($page_title) }}">
            @yield('header')
            @yield('body')
            @yield('footer')
        </section>
    </body>
</html>
