@extends('app', ['page_title' => 'My Preferences', 'page_class' => 'user-preferences'])

@section('aside-content')
    @include('partials.sideMenu.dashboard', ['openDashboardDrawer' => false, 'openProjectDrawer' => false])
@stop

@section('header')
    <section class="head">
        <a class="back" href=""><i class="icon icon-chevron"></i></a>
        <div class="inner-wrap center">
            <h1 class="title">
                <i class="icon icon-check-circle"></i>
                <span class="name">My Preferences</span>
            </h1>
            <p class="description">Use the switches below to modify your kora preferences.</p>
        </div>
    </section>
@stop

@section('body')
    @include('partials.projects.notification')

    <section class="edit-preferences center">
    
    {!! Form::open(['method' => 'PATCH', 'url' => action('Auth\UserController@toggleOnboarding'), 'enctype' => 'multipart/form-data']) !!}
        <div class="form-group my-xxxl">
            <h2 class="sub-title">Replay Kora Introduction?</h2>
            <p><button type="submit" class="text underline-middle-hover">Replay Kora Introduction</button></p>
            {{ \App\Http\Controllers\Auth\UserController::returnUserPrefs('onboarding') }}
        </div>
    {!! Form::close() !!}

    <div class="form-group mt-xxxl">
        <div class="spacer"></div>
    </div>

    {!! Form::open(['method' => 'PATCH', 'url' => action('Auth\UserController@updatePreferences', ['uid' => $user->id]), 'enctype' => 'multipart/form-data', 'class' => ['edit-preferences-form']]) !!}
        @include('partials.user.preferences.form')
    {!! Form::close() !!}
    </section>
@stop

@section('javascripts')
    @include('partials.user.javascripts')

    <script type="text/javascript">
        Kora.User.Preferences();
    </script>
@stop