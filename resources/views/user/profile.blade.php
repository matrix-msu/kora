@extends('app', ['page_title' => (Auth::user()->id == $user->id ? 'My Profile' : $user->username), 'page_class' => 'user-profile'])

@section('aside-content')
  @include('partials.sideMenu.dashboard', ['openDashboardDrawer' => false, 'openProjectDrawer' => false])
@stop

@section('header')
    @include('partials.user.profile.head')
@stop

@section('body')
  @include('partials.projects.notification')
    <section class="center profile page-section page-section-js {{($section == 'profile' ? 'active' : '')}}" id="profile">
        <div class="attr mt-xl">
            <span class="title">First Name: </span>
            <span class="desc">{{$user->first_name}}</span>
        </div>

        <div class="attr mt-xl">
            <span class="title">Last Name: </span>
            <span class="desc">{{$user->last_name}}</span>
        </div>

        <div class="attr mt-xl">
            <span class="title">User Name: </span>
            <span class="desc">{{$user->username}}</span>
        </div>

        <div class="attr mt-xl">
            <span class="title">Email: </span>
            <span class="desc">{{$user->email}}</span>
        </div>

        <div class="attr mt-xl">
            <span class="title">Organization: </span>
            <span class="desc">{{$user->organization}}</span>
        </div>
    </section>
@stop


@section('javascripts')
    @include('partials.user.javascripts')

    <script type="text/javascript">
        Kora.User.Profile();
    </script>
@stop

