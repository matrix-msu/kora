@extends('app', ['page_title' => 'Error 500', 'page_class' => 'error-500'])


@section('body')
  <div class="content">
    <div class="e500">
        <div class="form-container center">
			<div class="subheader mt-m">Whoops, something went wrong.</div>
			<div class="main-info mt-xxxl">Feel free to contact the Installation Admin</div>
			<a href="mailto:{{$install_admin_email}}" class="link">{{$install_admin_email}}</a>
			<div class="main-info">about this problem, or ...</div>
			
			<button id="back-button" class="footer-spacing btn mt-xxxl" type="submit">Go Back</button>
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
		$("#back-button").click(function() {
			history.back(-1);
		});
	</script>
@stop