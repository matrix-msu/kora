@extends('app', ['page_title' => 'Welcome to Kora', 'page_class' => 'welcome'])

@section('body')
@include('partials.projects.notification')
<div class="content">
  <div class="form-container center">
    <img class="logo" src="{{ url('assets/logos/logo_green_text_dark.svg') }}">

    <div>
      <form class="form-horizontal" role="form" method="POST" action="{{ url('/login') }}">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">

        <div class="form-group mt-xxxl">
          {!! Form::label('email', 'Your Username or Email') !!}
            <span class="error-message">{{array_key_exists("email", $errors->messages()) ? $errors->messages()["email"][0] : ''}}</span>
          {!! Form::text('email', null, ['class' => 'text-input'. (array_key_exists("email", $errors->messages()) ? ' error' : ''), 'placeholder' => 'Enter your username or email here', 'value' => old('email'), 'autofocus']) !!}
        </div>

        <div class="form-group mt-xl">
          {!! Form::label('password', 'Your Password') !!}
            <span class="error-message">{{array_key_exists("password", $errors->messages()) ? $errors->messages()["password"][0] : ''}}</span>
          {!! Form::password('password', ['class' => 'text-input'. (array_key_exists("password", $errors->messages()) ? ' error' : ''), 'placeholder' => 'Enter your password here']) !!}
        </div>

        <div class="form-group mt-xxxl memory">
          <div class="check-box-half">
            <input type="checkbox"
                   value="1"
                   class="check-box-input"
                   id="remember"
                   name="remember" />
            <span class="check"></span>
            <span class="placeholder">Remember Me</span>
          </div>
        </div>

        <div class="form-group mt-xxxl">
          <button type="submit" class="btn btn-primary">Login</button>
        </div>

          @if(preg_match('/^http(s)?:\/\/[a-z0-9-]+(\.[a-z0-9-]+)*(:[0-9]+)?(\/.*)?$/i', config('services.gitlab.host')))
          <div class="form-group center mt-xxxl">
              <a href="{{ action('Auth\LoginController@redirectToGitlab') }}" class="btn half-sub-btn extend-mobile" data-unsp-sanitized="clean">Login with Gitlab</a>
          </div>
          @endif

          @if(config('auth.public_registration'))
            <p class="mt-xxxl mb-0"><a class="text underline-middle-hover" href="{{ url('/register') }}">Need to Sign Up?</a></p>
          @endif
      </form>
    </div>
  </div>
</div>
@stop

@section('javascripts')
    {!! Minify::javascript([
		'/assets/javascripts/vendor/jquery/jquery.js',
		'/assets/javascripts/vendor/jquery/jquery-ui.js'
	])->withFullUrl() !!}
@stop
