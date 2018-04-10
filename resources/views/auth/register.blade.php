@extends('app', ['page_title' => 'Sign Up', 'page_class' => 'register'])

@section('body')
<div class="content">
  <div class="form-container center">
    <section class="head">
      <h1 class="title">Sign Up</h1>
    </section>

    @if (count($errors) > 0)
      <div class="alert alert-danger">
        <strong>Whoops!</strong> There were some problems with your input.<br><br>
        <ul>
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form id="register-form" class="form-horizontal form-file-input" role="form" method="POST" enctype="multipart/form-data" action="{{ url('/register') }}">
      <input type="hidden" id="_token" name="_token" value="{{ csrf_token() }}">
      <input type="hidden" id="regtoken" name="regtoken" value="{{\App\Http\Controllers\Auth\RegisterController::makeRegToken()}}">

      <div class="form-group half mt-xl">
        <label for="first-name">Your First Name</label>
  			<input type="text" class="text-input" id="first_name" name="first_name" placeholder="Enter your first name here" value="{{ old('first_name') }}">
      </div>

      <div class="form-group half mt-xl">
        <label for="first-name">Your Last Name</label>
  			<input type="text" class="text-input" id="last_name" name="last_name" placeholder="Enter your last name here" value="{{ old('last_name') }}">
      </div>

      <div class="form-group mt-xl">
        <label for="username">Your Username</label>
        <input type="text" class="text-input" id="username" name="username" placeholder="Enter your username here" value="{{ old('username') }}">
      </div>

      <div class="form-group mt-xl">
        <label for="email">Your Email</label>
        <input type="email" class="text-input" id="email" name="email" placeholder="Enter your email here" value="{{ old('email') }}">
      </div>

      <div class="form-group half mt-xl">
        <label for="password">Your Password</label>
  			<input type="password" class="text-input" id="password" name="password" placeholder="Enter your password here">
      </div>

      <div class="form-group half mt-xl">
        <label for="password_confirmation">Confirm Your Password</label>
  			<input type="password" class="text-input" id="password_confirmation" name="password_confirmation" placeholder="Confirm your password here">
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
  			<input type="text" class="text-input" id="organization" name="organization" placeholder="Enter your organization here" value="{{ old('organization') }}">
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

      <div class="form-group mt-xxxl" >
          <button type="submit" class="btn btn-primary">Sign Up</button>
      </div>
    </form>
  </div>
</div>
@stop

@section('javascripts')
  @include('partials.auth.javascripts')

  <script>
    Kora.Auth.Register();
  </script>

  <!-- Google reCAPTCHA -->
  <script type="text/javascript" src="https://www.google.com/recaptcha/api.js" async defer></script>
@stop
