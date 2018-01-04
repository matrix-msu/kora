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

            <p class="forgot my-0"><a class="text underline-middle-hover" href="{{ url('/password/email') }}">Forgot Password?</a></p>
          </div>

          <div class="form-group mt-xxxl">
            <button type="submit" class="btn btn-primary">Login</button>
          </div>

          <p class="mt-xxxl mb-0"><a class="text  underline-middle-hover" href="{{ url('/auth/register') }}">Need to Sign Up?</a></p>
        </form>
      </div>
    </div>
  @endif
</div>
@stop


@section('footer')
@stop
