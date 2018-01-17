@extends('app', ['page_title' => 'Welcome to Kora', 'page_class' => 'welcome'])

@section('body')
<div class="content">
  <div class="form-container py-100-xl ma-auto">
    <div>
      @if(!isset($not_installed))
        <img src="{{ env('BASE_URL') }}logos/koraiii-logo-blue.svg">
      @else
        <img src="logos/koraiii-logo-blue.svg">
      @endif
    </div>

    @if (Auth::guest() && !isset($not_installed))
      <div>
        @if (count($errors) > 0)
          <div class="error-alert">
            <strong>Whoops!</strong> There were some problems with your input.<br><br>
            <ul>
              @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        <form class="form-horizontal" role="form" method="POST" action="{{ url('/auth/login') }}">
          <input type="hidden" name="_token" value="{{ csrf_token() }}">

          <div class="form-group mt-xxxl">
            {!! Form::label('email', 'Your Username or Email') !!}
            {!! Form::text('email', null, ['class' => 'text-input', 'placeholder' => 'Enter your username or email here', 'value' => old('email'), 'autofocus']) !!}
          </div>

          <div class="form-group mt-xl">
            {!! Form::label('password', 'Your Password') !!}
            {!! Form::password('password', ['class' => 'text-input', 'placeholder' => 'Enter your password here']) !!}
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

          <p class="mt-xxxl mb-0"><a class="text  underline-middle-hover" href="{{ url('/auth/register') }}">Need to Sign Up?</a></p>
        </form>
      </div>
    </div>
  @elseif (Auth::guest() && !isInstalled())
    <div class="kora3 mt-xxl">
        Kora 3
    </div>

    <div class="ready mt-xxl">
        Ready for Initialization
    </div>

    <div class="commander mt-m">
        We are ready to begin the Kora Initialization sequence, Commander.
        Ready when you are.
    </div>

    <form class="form-horizontal" role="form" method="GET" action="{{ url('/install') }}">
        <div class="form-group mt-xxl">
            <button type="submit" class="btn btn-primary">Begin Initialization Sequence</button>
        </div>
    </form>
  @endif
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
      @if (count($errors) > 0)
        <div class="alert alert-danger">
          <strong>{{trans('auth_password.whoops')}}!</strong> {{trans('auth_password.problems')}}.<br><br>
          <ul>
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <form class="form-horizontal" role="form" method="POST" action="{{ url('/password/email') }}">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">

        <div class="form-group mt-xl">
          <label for="email">Enter Your Email to Recover Password</label>
          <input type="email" class="text-input" name="email" value="{{ old('email') }}" placeholder="Enter your email here">
        </div>

        <div class="form-group mt-xl">
          <button type="submit" class="btn btn-primary">Send Password Reset Link</button>
        </div>
      </form>
    </div>
  </div>
</div>


@stop

@section('javascripts')
  <script>
    Kora.Modal.initialize();

    $('.password-js').click(function(e) {
      e.preventDefault();

      Kora.Modal.open();
    });
  </script>
@stop

