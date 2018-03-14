@extends('app', ['page_title' => 'My Profile', 'page_class' => 'my-profile'])

@section('header')
    <section class="head">
        <div class="inner-wrap center">
            <h1 class="title">
                @if(!is_null($profile) && File::exists( config('app.base_path') . '/public/app/' . $profile ))
                    <img class="profile-picture" src="{{config('app.storage_url') . $profile}}">
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
            <p class="description"></p>
        </div>
    </section>
@stop

@section('body')
    <section class="center my-profile-attributes">
        <div class="mt-xl">
            <span class="attr-title">First Name: </span>
            <span class="attr-desc">{{$user->first_name}}</span>
        </div>

        <div class="mt-xl">
            <span class="attr-title">Last Name: </span>
            <span class="attr-desc">{{$user->last_name}}</span>
        </div>

        <div class="mt-xl">
            <span class="attr-title">User Name: </span>
            <span class="attr-desc">{{$user->username}}</span>
        </div>

        <div class="mt-xl">
            <span class="attr-title">Email: </span>
            <span class="attr-desc">{{$user->email}}</span>
        </div>

        <div class="mt-xl">
            <span class="attr-title">Organization: </span>
            <span class="attr-desc">{{$user->organization}}</span>
        </div>
    </section>
@stop

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h1>{{trans('user_profile.heading',['name'=>Auth::user()->name])}}</h1>
                    </div>

                    <div class="panel-body">

                        @if(!is_null($profile))
                            <img id="current_profile_pic" style="width:auto;height:200px" src="{{config('app.storage_url') . 'profiles/'.\Auth::user()->id.'/'.$profile}}">
                        @else
                            <img id="current_profile_pic" style="width:auto;height:200px" src="{{config('app.url') . 'assets/images/blank_profile.jpg'}}">
                        @endif

                        <div class="form-group">
                            {!! Form::file('profile', ['class' => 'form-control', 'accept' => '.jpeg,.png,.bmp,.gif,.jpg', 'id' => 'profile_pic']) !!}
                            <button type="button" id="submit_profile_pic" class="btn btn-default">Update Profile Picture</button>
                        </div>

                        @if(\Auth::user()->id == 1)
                        <div class="form-group">
                            <button type="button" id="order_66" class="btn btn-default btn-danger">Order 66</button>
                        </div>
                        @endif

                        <h3>{{trans('user_profile.info')}}:</h3>

                        @include('partials.changeprofile')

                        <hr>

                        @include('partials.changepassword')

                        <hr>

                        @include('partials.showpermissions')

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('footer')
    @include('partials.profile.javascripts')

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