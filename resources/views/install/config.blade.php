@extends('app', ['page_title' => "Edit Config", 'page_class' => 'edit-config'])

@section('aside-content')
  @php $openManagement = true @endphp
  @include('partials.sideMenu.dashboard', ['openDashboardDrawer' => false, 'openProjectDrawer' => false])
@stop

@section('header')
    <section class="head">
        <a class="back" href=""><i class="icon icon-chevron"></i></a>
        <div class="inner-wrap center">
            <h1 class="title">
                <i class="icon icon-projects"></i>
                <span>Kora Configuration File</span>
            </h1>
            <p class="description">Here you can edit the configuration file for this Kora Installation. This includes
                updating ReCaptcha keys, which are used for User Registration, and updating your server/host settings for emails in Kora 3</p>
        </div>
    </section>
@stop

@section('body')
    <section class="edit-config-form center">
        <form method="post" action="{{action("InstallController@updateEnvConfigs")}}" class="config-form">
            <input type="hidden" name="_token" value="{{csrf_token()}}">
            @foreach($configs as $config)
                <div class="form-group mt-xl">
                    <label for="{{$config['slug']}}">{{$config['title']}}</label>
                    <span class="error-message"></span>
                    <input id="{{$config['slug']}}" name="{{$config['slug']}}" class="text-input" @if($config['slug']=='mail_password') type="password" @else type="text"@endif value="{{$config['value']}}">
                </div>
            @endforeach
            <div class="form-group mt-xxl">
                <button class="btn btn-primary validate-config-js" type="submit">Update Configuration File</button>
            </div>
        </form>
    </section>
@stop

@section('javascripts')
    @include('partials.install.javascripts')

    <script type="text/javascript">
        Kora.Install.Config();
    </script>
@stop
