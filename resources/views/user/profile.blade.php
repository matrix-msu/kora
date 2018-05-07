@extends('app', ['page_title' => 'My Profile', 'page_class' => 'user-profile'])

@section('aside-content')
  @include('partials.sideMenu.dashboard', ['openDashboardDrawer' => false, 'openProjectDrawer' => false])
@stop

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
                <a href="#profile" class="section select-section-js underline-middle underline-middle-hover toggle-by-name">Profile</a>
                <a href="#permissions" class="section select-section-js underline-middle underline-middle-hover">Permissions</a>
                <a href="#recordHistory" class="section select-section-js underline-middle underline-middle-hover">Record History</a>
            </div>
        </div>
    </section>
@stop

@section('body')
    <section class="center page-section profile page-section-js" id="profile">
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

    <section class="center page-section permissions page-section-js" id="permissions">
        <div class="filters mt-xxxl">
            <a href="#projects" class="filter-link select-content-section-js underline-middle underline-middle-hover">Projects</a>
            <a href="#forms" class="filter-link select-content-section-js underline-middle underline-middle-hover">Forms</a>
        </div>
        <div class="content-section content-section-js" id="projects">
            @if (!$user->admin)
                <div class="my-xl">
                    <p>{{$user->first_name}} has access to the following projects...</p>
                </div>
                @foreach ($projects as $index=>$project)
                    @include('partials.user.profile.project')
                @endforeach
            @else
                <div class="my-xl">
                    <p>{{$user->first_name}} has access to all projects</p>
                </div>
            @endif
        </div>
        <div class="content-section content-section-js" id="forms">
            @if (!$user->admin)
                <div class="my-xl">
                    <p>{{$user->first_name}} has access to the following forms...</p>
                </div>
                @foreach ($forms as $index=>$form)
                    @include('partials.user.profile.form')
                @endforeach
            @else
                <div class="my-xl">
                    <p>{{$user->first_name}} has access to all forms</p>
                </div>
            @endif
        </div>
    </section>

    <section class="center page-section record-history page-section-js" id="recordHistory">
        <div class="filters mt-xxxl">
            <a href="#recentlyModified" class="filter-link select-content-section-js underline-middle underline-middle-hover">Recently Modified</a>
            <a href="#myCreatedRecords" class="filter-link select-content-section-js underline-middle underline-middle-hover">My Created Records</a>
        </div>
        <div class="content-section content-section-js" id="recentlyModified">
            <div class="my-xl">
                <p>You have recently modified the following {{count($records)}} records...</p>
            </div>
            @foreach ($records as $index=>$record)
                @include('partials.user.profile.record')
            @endforeach
        </div>
        <div class="content-section content-section-js" id="myCreatedRecords">
            <h1>My Created Recrods</h1>
        </div>
    </section>
@stop


@section('javascripts')
    @include('partials.user.javascripts')

    <script type="text/javascript">
        Kora.User.Profile();
    </script>

    {{--<script>--}}
        {{--$( ".panel-heading" ).on( "click", function() {--}}
            {{--if ($(this).siblings('.collapseTest').css('display') == 'none' ){--}}
                {{--$(this).siblings('.collapseTest').slideDown();--}}
            {{--}else {--}}
                {{--$(this).siblings('.collapseTest').slideUp();--}}
            {{--}--}}
        {{--});--}}

        {{--$( "#submit_profile_pic" ).on( "click", function() {--}}
            {{--var fd = new FormData();--}}
            {{--fd.append( 'profile', $('#profile_pic')[0].files[0] );--}}
            {{--fd.append( '_token', "{{ csrf_token() }}" );--}}

            {{--$.ajax({--}}
                {{--url: "{{action('Auth\UserController@changepicture')}}",--}}
                {{--method:'POST',--}}
                {{--data: fd,--}}
                {{--contentType: false,--}}
                {{--processData: false,--}}
                {{--success: function(data){--}}
                    {{--$("#current_profile_pic").attr("src",data);--}}
                {{--}--}}
            {{--});--}}
        {{--});--}}

        {{--@if(\Auth::user()->id == 1)--}}
        {{--$( "#order_66" ).on( "click", function() {--}}
            {{--var encode = $('<div/>').html("Are you sure, Emperor?").text();--}}
            {{--var resp1 = confirm(encode);--}}
            {{--if(resp1) {--}}
                {{--var enc1 = $('<div/>').html("This is your last warning! EVERYTHING in Kora will be removed permanently!!!").text();--}}
                {{--var enc2 = $('<div/>').html("Type DELETE to execute Order 66").text();--}}
                {{--var resp2 = prompt(enc1 + '!', enc2 + '.');--}}
                {{--// User must literally type "DELETE" into a prompt.--}}
                {{--if(resp2 === 'DELETE') {--}}

                    {{--$("#slideme").slideToggle(2000, function() {--}}
                        {{--$('#progress').slideToggle(400);--}}
                    {{--});--}}
                    {{--$.ajax({--}}
                        {{--url: "{{action('AdminController@deleteData')}}",--}}
                        {{--method:'POST',--}}
                        {{--data: {--}}
                            {{--"_token": "{{ csrf_token() }}",--}}
                            {{--"order_66": "EXECUTE"--}}
                        {{--},--}}
                        {{--success: function(data){--}}
                            {{--console.log(data);--}}
                        {{--}--}}
                    {{--});--}}
                {{--}--}}
            {{--}--}}
        {{--});--}}
        {{--@endif--}}

        {{--function updateLanguage(selected_lang){--}}
            {{--changeProfile("lang",selected_lang);--}}
        {{--}--}}

        {{--function updateHomePage(dash){--}}
            {{--changeProfile("dash",dash);--}}
        {{--}--}}

       {{--function updateOrganization(){--}}
           {{--changeProfile("org",$("#organization").val());--}}
       {{--}--}}
       {{--function updateRealName(){--}}
           {{--changeProfile("name",$("#realname").val());--}}
       {{--}--}}

        {{--function changeProfile(rtype,rvalue){--}}
            {{--var updateURL ="{{action('Auth\UserController@changeprofile')}}";--}}
            {{--$.ajax({--}}
                {{--url:updateURL,--}}
                {{--method:'POST',--}}
                {{--data: {--}}
                    {{--"_token": "{{ csrf_token() }}",--}}
                    {{--"type": rtype,--}}
                    {{--"field": rvalue--}}
                {{--},--}}
                {{--success: function(data){--}}
                    {{--window.location.replace('{{action('Auth\UserController@index')}}');--}}
                {{--}--}}
            {{--});--}}
        {{--}--}}

    {{--</script>--}}

@stop
