@extends('app', ['page_title' => 'Welcome to Kora', 'page_class' => 'welcome'])

@section('body')
@include('partials.projects.notification')
<div class="content">
  <div class="form-container center">
    <img class="logo" src="{{ url('assets/logos/koraiii-logo-blue.svg') }}">

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

          <p class="forgot my-0"><a class="text password-js underline-middle-hover" href="#">Forgot Password?</a></p>
        </div>

        <div class="form-group mt-xxxl">
          <button type="submit" class="btn btn-primary">Login</button>
        </div>

        <p class="mt-xxxl mb-0"><a class="text underline-middle-hover" href="{{ url('/register') }}">Need to Sign Up?</a></p>
      </form>
    </div>
  </div>
</div>

<div class="modal modal-js modal-mask">
  <div class="content">
    <div class="header">
      <span class="title">Forgot Your Password?</span>
      <a href="#" class="modal-toggle modal-toggle-js">
        <i class="icon icon-cancel"></i>
      </a>
    </div>
    <div class="body">
      <form class="form-horizontal pass-form-js" role="form" method="POST" action="{{ url('/password/email') }}">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">

        <div class="form-group mt-xxl">
          <label for="email">Enter Your Email to Recover Password</label>
            <span class="error-message pass-error-js">{{array_key_exists("email", $errors->messages()) ? $errors->messages()["email"][0] : ''}}</span>
          <input type="email" class="text-input pass-email-js {{(array_key_exists("email", $errors->messages()) ? ' error' : '')}}" name="email" value="{{ old('email') }}" placeholder="Enter your email here">
        </div>

        <div class="form-group mt-xxl">
          <button type="submit" class="btn btn-primary pass-reset-js">Send Password Reset Link</button>
        </div>
      </form>
    </div>
  </div>
</div>
@stop

@section('javascripts')
  @include('partials.auth.javascripts')
  
  <script type="text/javascript">
    var CSRFToken = '{{ csrf_token() }}';
    var emailURL = '{{ action('Auth\ResetPasswordController@preValidateEmail') }}';
  </script>

  <script>
    Kora.Modal.initialize();

    $('.password-js').click(function(e) {
      e.preventDefault();

      Kora.Modal.open();
    });

    $('.pass-reset-js').click(function(e) {
        e.preventDefault();

        var email = $('.pass-email-js').val();

        if(email=='') {
            $('.pass-error-js').text('The email field is required.');
            $('.pass-email-js').addClass('error');
        } else if(!validateEmail(email)) {
            $('.pass-error-js').text('The email must be a valid email address.');
            $('.pass-email-js').addClass('error');
        } else {
			display_loader();
			
			$.ajax({
				url: emailURL,
				method: 'POST',
				data: {
					"_token": CSRFToken,
					"email": email
				},
				success: function(data) {
					var response = data.response
					
					if (response == "Found") {
						$('.pass-error-js').text('');
						setTimeout(function(){
							$('.pass-form-js').submit();
						}, 10);
					}
				},
				error: function(data) {
					var response = data.responseJSON.response;
					
					$('.pass-error-js').text(response);
					$('.pass-email-js').addClass('error');
					hide_loader();
				}
			});
        }
    });

    function validateEmail(email) {
        var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return re.test(String(email).toLowerCase());
    }
  </script>
@stop
