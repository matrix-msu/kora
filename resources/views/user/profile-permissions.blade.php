@extends('app', ['page_title' => 'My Profile', 'page_class' => 'user-profile'])

@section('header')
    <section class="head">
        <div class="inner-wrap center">
            <h1 class="title">
                @if ($user->profile)
                    <img class="profile-pic" src="{{ $user->getProfilePicUrl() }}" alt="Profile Pic">
                @else
                    <i class="icon icon-user"></i>
                @endif
                <span class="ml-m">{{$user->first_name}} {{$user->last_name}}</span>
                @if(\Auth::user()->admin | \Auth::user()->id==$user->id)
                    <a href="{{ action('Auth\UserController@editProfile',['uid' => $user->id]) }}" class="head-button">
                        <i class="icon icon-edit right"></i>
                    </a>
                @endif
            </h1>
            <div class="content-sections">
                <a href="{{url('user', ['uid' => $user->id])}}" class="section select-section-js underline-middle underline-middle-hover">Profile</a>
                <a href="{{url('user', ['uid' => $user->id, 'section' => 'permissions'])}}" class="section select-section-js underline-middle underline-middle-hover active">Permissions</a>
                <a href="{{url('user', ['uid' => $user->id, 'section' => 'history'])}}" class="section select-section-js underline-middle underline-middle-hover">Record History</a>
            </div>
        </div>
    </section>
@stop

@section('body')
        <section class="center page-section page-section-js {{($section == 'permissions' ? 'active' : '')}}" id="permissions">
        <div class="section-filters mt-xxxl">
            <a href="#projects" class="filter-link select-content-section-js underline-middle underline-middle-hover">Projects</a>
            <a href="#forms" class="filter-link select-content-section-js underline-middle underline-middle-hover">Forms</a>
        </div>
        <div class="content-section content-section-js" id="projects">
            @if (!$user->admin)
                <div class="my-xl">
                    <p>@if (Auth::user()->id == $user->id) You have @else {{$user->first_name}} has @endif  access to the following projects...</p>
                </div>
                @foreach ($projects as $index=>$project)
                    @include('partials.user.profile.project')
                @endforeach
            @else
                <div class="my-xl">
                    <p>@if (Auth::user()->id == $user->id) You have @else {{$user->first_name}} has @endif access to all projects</p>
                </div>
            @endif
        </div>
        <div class="content-section content-section-js" id="forms">
            @if (!$user->admin)
                <div class="my-xl">
                    <p>@if (Auth::user()->id == $user->id) You have @else {{$user->first_name}} has @endif access to the following forms...</p>
                </div>
                @foreach ($forms as $index=>$form)
                    @include('partials.user.profile.form')
                @endforeach
            @else
                <div class="my-xl">
                    <p>@if (Auth::user()->id == $user->id) You have @else {{$user->first_name}} has @endif access to all forms</p>
                </div>
            @endif
        </div>
    </section>
@stop


@section('javascripts')
    @include('partials.user.javascripts')

    <script type="text/javascript">
        Kora.User.Profile();
    </script>
@stop