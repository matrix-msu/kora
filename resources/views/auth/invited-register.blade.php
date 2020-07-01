@extends('app', ['page_title' => 'Sign Up', 'page_class' => 'invited-register'])

@section('body')
<div class="content">
  <div class="form-container center">
    <img class="logo" src="{{ url('assets/logos/logo_green_text_dark.svg') }}">
    <section class="head">
      <h1 class="title">Welcome to Kora!</h1>
      <h3 class="sub-title">Let's set up your account.</h3>
    </section>

    @if(count($errors) > 0)
      <div class="alert alert-danger">
        <strong>Whoops!</strong> There were some problems with your input.<br><br>
        <ul>
          @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form id="register-form" class="form-horizontal form-file-input user-form" role="form" method="POST" enctype="multipart/form-data" action="{{ action('Auth\UserController@updateFromEmail', $user->id) }}">
      <input type="hidden" id="_method" name="_method" value="PATCH">
      <input type="hidden" id="_token" name="_token" value="{{ csrf_token() }}">
      <input type="hidden" id="regtoken" name="regtoken" value="{{\App\Http\Controllers\Auth\RegisterController::makeRegToken()}}">

      <div class="form-group half mt-xl">
        <label for="first-name">Your First Name</label>
          <span class="error-message">{{array_key_exists("first_name", $errors->messages()) ? $errors->messages()["first_name"][0] : ''}}</span>
  			<input type="text" class="text-input {{(array_key_exists("first_name", $errors->messages()) ? ' error' : '')}}"
                   id="first_name" name="first_name" placeholder="Enter your first name here" value="">
      </div>

      <div class="form-group half mt-xl">
        <label for="first-name">Your Last Name</label>
          <span class="error-message">{{array_key_exists("last_name", $errors->messages()) ? $errors->messages()["last_name"][0] : ''}}</span>
  			<input type="text" class="text-input {{(array_key_exists("last_name", $errors->messages()) ? ' error' : '')}}"
                   id="last_name" name="last_name" placeholder="Enter your last name here" value="">
      </div>

      <div class="form-group mt-xl">
        <label for="username">Your Username</label>
          <span class="error-message">{{array_key_exists("username", $errors->messages()) ? $errors->messages()["username"][0] : ''}}</span>
        <input type="text" class="text-input {{(array_key_exists("username", $errors->messages()) ? ' error' : '')}}"
               id="username" name="username" placeholder="Enter your username here" value="{{ $user->username }}">
      </div>

      <div class="form-group mt-xl">
        <label for="email">Your Email (You can change this later)</label>
          <span class="error-message">{{array_key_exists("email", $errors->messages()) ? $errors->messages()["email"][0] : ''}}</span>
        <input readonly type="email" class="text-input {{ (array_key_exists("email", $errors->messages()) ? ' error' : '') }}"
               id="invited-email" name="email" placeholder="Enter your email here" value="{{ $user->email }}">
      </div>

      <div class="form-group half mt-xl">
        <label for="password">Your Password</label>
          <span class="error-message">{{array_key_exists("password", $errors->messages()) ? $errors->messages()["password"][0] : ''}}</span>
  			<input type="password" class="text-input {{(array_key_exists("password", $errors->messages()) ? ' error' : '')}}"
                   id="password" name="password" placeholder="Enter your password here">
      </div>

      <div class="form-group half mt-xl">
        <label for="password_confirmation">Confirm Your Password</label>
          <span class="error-message">{{array_key_exists("password_confirmation", $errors->messages()) ? $errors->messages()["password_confirmation"][0] : ''}}</span>
  			<input type="password" class="text-input {{(array_key_exists("password_confirmation", $errors->messages()) ? ' error' : '')}}"
                   id="password_confirmation" name="password_confirmation" placeholder="Confirm your password here">
      </div>

      <div class="form-group mt-xl">
        <label>Your Profile Image</label>
        <input type="file" accept="image/*" name="profile" id="profile" class="profile-input" />
        <label for="profile" class="profile-label">
          <div class="icon-user-cont"><i class="icon icon-user"></i></div>
          <p class="filename">Add a photo to help others identify you</p>
          <p class="instruction mb-0">
            <span class="dd">Drag and Drop or Select a Photo here</span>
            <span class="no-dd">Select a Photo here</span>
            <span class="select-new">Select a Different Photo?</span>
          </p>
        </label>
      </div>

      <div class="form-group mt-xl">
        <label for="organization">Your Organization</label>
          <span class="error-message">{{array_key_exists("organization", $errors->messages()) ? $errors->messages()["organization"][0] : ''}}</span>
  			<input type="text" class="text-input {{(array_key_exists("organization", $errors->messages()) ? ' error' : '')}}"
                   id="organization" name="organization" placeholder="Enter your organization here" value="">
      </div>

      {{--
      <div class="form-group">
          <label for="language">Language</label>
              <input type="text" class="form-control" name="language" value="{{ App::getLocale() }}">
      </div> --}}

      <div class="form-group mt-xl">
          <label for="language">Language</label>
          <select id="language" name="language" class="chosen-select">
              {{$languages_available = Config::get('app.locales_supported')}}
              @foreach($languages_available->keys() as $lang)
                  <option value='{{$languages_available->get($lang)[0]}}'>{{$languages_available->get($lang)[1]}} </option>
              @endforeach
          </select>
      </div>

      <div class="form-group mt-xxxl">
          <div style="padding: 5px" align="center" class="g-recaptcha" data-sitekey="{{ config('auth.recap_public') }}"></div>
      </div>

      @if( (session()->has('notification') && session('notification')['message'] == "ReCaptcha validation error")
              || config('auth.recap_public')=='' || config('auth.recap_private')=='')
          <div class="form-group mt-xxxl" >
              <button type="submit" class="btn btn-primary warning disabled">Missing reCAPTCHA Keys</button>

              <p>Please add or update the reCAPTCHA keys in order to complete registration. Contact your system administrator if you need assistance.</p>
          </div>
      @else
          <div class="form-group mt-xxxl" >
              <button type="submit" class="btn btn-primary validate-user-js">Sign Up</button>
          </div>
      @endif
    </form>
  </div>
</div>
@stop

@section('javascripts')
  @include('partials.auth.invited-javascripts')

  <script>
    var validationUrl = '{{action('Auth\UserController@validateUserFields',['uid'=>$user->id])}}';
    CSRFToken = '{{ csrf_token() }}';

    Kora.Auth.Register();
  </script>

  <!-- Google reCAPTCHA -->
  <script type="text/javascript" src="https://www.google.com/recaptcha/api.js" async defer></script>
@stop
