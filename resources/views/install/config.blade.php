@extends('app', ['page_title' => "Edit Config", 'page_class' => 'edit-config'])

@section('aside-content')
  <?php $openManagement = true ?>
  @include('partials.sideMenu.dashboard', ['openDashboardDrawer' => false, 'openProjectDrawer' => false])
@stop

@section('header')
    <section class="head">
        <div class="inner-wrap center">
            <h1 class="title">
                <i class="icon icon-projects"></i>
                <span>Kora Configuration File</span>
            </h1>
            <p class="description">Brief info on Configuration File Management, followed by instructions on how to use
                the Configuration File Management page will go here.</p>
        </div>
    </section>
@stop

@section('body')
    <section class="edit-config-form center">
        <form method="post" action={{action("InstallController@updateEnvConfigs")}}>
            <input type="hidden" name="_token" value="{{csrf_token()}}">
            @foreach($configs as $config)
                <div class="form-group mt-xl">
                    <label for="{{$config['slug']}}">{{$config['title']}}</label>
                    <input id="{{$config['slug']}}" name="{{$config['slug']}}" class="text-input" @if($config['slug']=='mail_password') type="password" @else type="text"@endif value="{{$config['value']}}">
                </div>
            @endforeach
            <div class="form-group mt-xxl">
                <button class="btn btn-primary" type="submit">Update Configuration File</button>
            </div>
        </form>
    </section>
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
@stop
