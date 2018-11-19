@extends('app', ['page_title' => 'Error 404', 'page_class' => 'error-404'])

@section('body')
  <div class="content">
    <div class="e404">
        <div class="form-container center">
            <div class="header">404</div>
			
			<div class="subheader mt-sm">We couldn't find the page you're looking for.</div>
			
			<div class="main-info mt-xxl">Feel free to contact the Installation Admin</div>
			
			<a href="mailto:{{$install_admin_email}}" class="link main-info">{{$install_admin_email}}</a>
			
			<div class="main-info">about this problem, or ...</div>
			
			<button id="home-button" class="footer-spacing btn mt-xl" type="submit">Go to Kora Home</button>
        </div>
    </div>
  </div>
@endsection

@section('footer')
	@include('partials.footer')
@endsection

@section('aside-content')
    <?php $openManagement = false; ?>
    @include('partials.sideMenu.dashboard', ['openDashboardDrawer' => false, 'openProjectDrawer' => false])
@stop

@section('javascripts')
	{!! Minify::javascript([
	'/assets/javascripts/vendor/jquery/jquery.js',
	'/assets/javascripts/vendor/jquery/jquery-ui.js',
	'/assets/javascripts/vendor/chosen.js',
	'/assets/javascripts/general/modal.js',
	'/assets/javascripts/navigation/navigation.js',
	'/assets/javascripts/general/global.js'
	])->withFullUrl() !!}
	
	<script type="text/javascript">
		$("#home-button").click(function() {
			window.location.replace("{{url('/home')}}");
		});
	</script>
@stop