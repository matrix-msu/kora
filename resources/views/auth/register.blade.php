@extends('app', ['page_title' => 'Sign Up', 'page_class' => 'register'])

@section('body')
<div class="content">
  <div class="form-container center">
    <section class="head">
      <h1 class="title">Sign Up</h1>
    </section>

    @if (count($errors) > 0)
      <div class="alert alert-danger">
          <strong>{{trans('auth_register.whoops')}}!</strong> {{trans('auth_register.problems')}}.<br><br>
          <ul>
              @foreach ($errors->all() as $error)
                  <li>{{ $error }}</li>
              @endforeach
          </ul>
      </div>
    @endif

    <form class="form-horizontal" role="form" method="POST" enctype="multipart/form-data" action="{{ url('/auth/register') }}">
      <input type="hidden" name="_token" value="{{ csrf_token() }}">
      <input type="hidden" name="regtoken" value="{{\App\Http\Controllers\Auth\AuthController::makeRegToken()}}">

      <div class="form-group half mt-xl pr-m">
        <label for="first-name">Your First Name</label>
  			<input type="text" class="text-input" name="first-name" placeholder="Enter your first name here" value="{{ old('name') }}">
      </div>

      <div class="form-group half mt-xl pl-m">
        <label for="first-name">Your Last Name</label>
  			<input type="text" class="text-input" name="last-name" placeholder="Enter your last name here" value="{{ old('name') }}">
      </div>

      <div class="form-group mt-xl">
        <label for="username">Your Username</label>
        <input type="text" class="text-input" name="username" placeholder="Enter your username here" value="{{ old('username') }}">
      </div>

      <div class="form-group mt-xl">
        <label for="email">Your Email</label>
        <input type="email" class="text-input" name="email" placeholder="Enter your email here" value="{{ old('email') }}">
      </div>

      <div class="form-group half mt-xl pr-m">
        <label for="password">Your Password</label>
  			<input type="password" class="text-input" name="password" placeholder="Enter your password here">
      </div>

      <div class="form-group half mt-xl pl-m">
        <label for="password_confirmation">Confirm Your Password</label>
  			<input type="password" class="text-input" name="password_confirmation" placeholder="Confirm your password here">
      </div>

      <div class="form-group mt-xl">
        <label>Your Profile Image</label>
        <input type="file" accept="image/*" name="profile-pic" id="profile-pic" class="profile-input" />
        <label for="profile-pic" class="profile-label">
          <img src="{{ env('BASE_URL') }}logos/blank_profile.jpg" height="80px" width="80px" alt="Profile">
          <p class="filename">Add a photo to help others identify you</p>
          <p class="instruction mb-0">Drag and Drop or Select a Photo here</p>
        </label>
      </div>

      <div class="form-group mt-xl">
        <label for="organization">Your Organization</label>
  			<input type="text" class="text-input" name="organization" placeholder="Enter your organization here" value="{{ old('organization') }}">
      </div>

      {{--
      <div class="form-group">
          <label for="language">Language</label>
              <input type="text" class="form-control" name="language" value="{{ App::getLocale() }}">
      </div> --}}

      <div class="form-group mt-xl">
          <label for="language">Language</label>
          <select name="language">
              {{$languages_available = Config::get('app.locales_supported')}}
              @foreach($languages_available->keys() as $lang)
                  <option value='{{$languages_available->get($lang)[0]}}'>{{$languages_available->get($lang)[1]}} </option>
              @endforeach
          </select>
      </div>

      <div class="form-group mt-xxxl">
          <div style="padding: 5px" align="center" class="g-recaptcha" data-sitekey="{{ env('RECAPTCHA_PUBLIC_KEY') }}"></div>
      </div>

      <div class="form-group mt-xxxl" >
          <button type="submit" class="btn btn-primary">Sign Up</button>
      </div>
    </form>
  </div>
</div>
@stop

@section('javascripts')
  @include('partials.javascripts')

  <script>
    var fileInput = $(".profile-input");
    var button = $(".profile-label");
    var pic = $(".profile-label img");
    var filename = $(".filename");
    var instruction = $(".instruction");

    function resetFileInput() {
      fileInput.replaceWith(fileInput.val('').clone(true));
      filename.html("Add a photo to help others identify you");
      instruction.html("Drag and Drop or Select a Photo here");
      pic.attr("src", "{{ env('BASE_URL') }}logos/blank_profile.jpg");
    };

    button.keydown(function(event) {
      if ( event.keyCode == 13 || event.keyCode == 32 ) {
          fileInput.focus();
      }
    });

    button.click(function(event) {
      fileInput.focus();
    });

    fileInput.change(function(event) {
      if (this.files && this.files[0]) {
        var reader = new FileReader();

        reader.onload = function (e) {
          pic.attr("src", e.target.result);
        };

        reader.readAsDataURL(this.files[0]);

        filename.html(this.value + "<span class='remove ml-xs'>x</span>");
        instruction.html("Select a Different Photo?");

        $(".remove").click(function(event) {
          event.preventDefault();
          resetFileInput();
        });
      }
    });
  </script>
@stop
