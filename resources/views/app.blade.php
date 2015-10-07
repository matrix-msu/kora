<html>
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
	<!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css">
	<!-- Optional theme -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap-theme.min.css">
	<!-- Latest compiled and minified JavaScript -->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/js/bootstrap.min.js"></script>
    <!-- Google reCAPTCHA -->
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
	<!-- Files for select 2-->
	<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.0-rc.2/css/select2.min.css" rel="stylesheet" />
	<script src="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.0-rc.2/js/select2.min.js"></script>
    <!-- Brings in Lato font -->
    <link href='//fonts.googleapis.com/css?family=Lato:100' rel='stylesheet' type='text/css'>
    @if(!isset($not_installed))
        <!-- For Rich Text -->
        <script src="{{ env('BASE_URL') }}public/ckeditor/ckeditor.js"></script>
        <!-- For Schedule -->
        <script type="text/javascript" src="{{ env('BASE_URL') }}public/bower_components/moment/min/moment.min.js"></script>
        <script type="text/javascript" src="{{ env('BASE_URL') }}public/bower_components/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js"></script>
        <link rel="stylesheet" href="{{ env('BASE_URL') }}public/bower_components/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css" />
        <link rel='stylesheet' href='{{ env('BASE_URL') }}public/bower_components/fullcalendar/dist/fullcalendar.css' />
        <script src='{{ env('BASE_URL') }}public/bower_components/fullcalendar/dist/fullcalendar.js'></script>
        <!-- For Geolocator -->
        <link rel="stylesheet" href="{{ env('BASE_URL') }}public/leaflet/leaflet.css" />
        <script src="{{ env('BASE_URL') }}public/leaflet/leaflet.js"></script>
		<!-- For Documents -->
		<link rel="stylesheet" href="{{ env('BASE_URL') }}public/fileUpload/css/style.css">
		<link rel="stylesheet" href="{{ env('BASE_URL') }}public/fileUpload/css/jquery.fileupload.css">
		<link rel="stylesheet" href="{{ env('BASE_URL') }}public/fileUpload/css/jquery.fileupload-ui.css">
		<script src="{{ env('BASE_URL') }}public/fileUpload/js/vendor/jquery.ui.widget.js"></script>
		<script src="{{ env('BASE_URL') }}public/fileUpload/js/jquery.iframe-transport.js"></script>
		<script src="{{ env('BASE_URL') }}public/fileUpload/js/jquery.fileupload.js"></script>
		<!-- For Gallery -->
		<link rel="stylesheet" type="text/css" href="{{ env('BASE_URL') }}public/slick/slick/slick.css"/>
		<link rel="stylesheet" type="text/css" href="{{ env('BASE_URL') }}public/slick/slick/slick-theme.css"/>
		<script type="text/javascript" src="//code.jquery.com/jquery-migrate-1.2.1.min.js"></script>
		<script type="text/javascript" src="{{ env('BASE_URL') }}public/slick/slick/slick.min.js"></script>
		<!-- For Playlist and Video -->
		<link rel="stylesheet" type="text/css" href="{{ env('BASE_URL') }}public/jplayer/pink.flag/css/jplayer.pink.flag.min.css"/>
		<script type="text/javascript" src="{{ env('BASE_URL') }}public/jplayer/jquery.jplayer.min.js"></script>
		<script type="text/javascript" src="{{ env('BASE_URL') }}public/jplayer/jplayer.playlist.min.js"></script>
		<!-- For 3D Model -->
		<script type="text/javascript" src="{{ env('BASE_URL') }}public/jsc3d/jsc3d.js"></script>
		<script type="text/javascript" src="{{ env('BASE_URL') }}public/jsc3d/jsc3d.webgl.js"></script>
		<script type="text/javascript" src="{{ env('BASE_URL') }}public/jsc3d/jsc3d.touch.js"></script>
		<!-- Dropdowns enhancement -->
		<link href="{{ env('BASE_URL') }}public/dropdown_enhancement/dist/css/dropdowns-enhancement.css" rel="stylesheet"/>
		<script type="text/javascript" src="{{ env('BASE_URL') }}public/dropdown_enhancement/dist/js/dropdowns-enhancement.js"></script>
	@endif
    <title>Kora 3</title>
</head>
<br />
<body>
		@if(isset($not_installed))
			@include('partials.install_nav')
		@else
			@include('partials.nav')
		@endif

    <div class="container">
		@include('flash::message')
	
        @yield('content')
    </div>

	
	<script>
		$('#flash-overlay-modal').modal();
		//$('div.alert').not('.alert-important').delay(3000).slideUp(300);
	</script>
    @yield('footer')
</body>
</html>