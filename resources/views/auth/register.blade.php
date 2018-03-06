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

    <form id="register-form" class="form-horizontal" role="form" method="POST" enctype="multipart/form-data" action="{{ url('/register') }}">
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
  <!-- Google reCAPTCHA -->
  <script type="text/javascript" src="https://www.google.com/recaptcha/api.js" async defer></script>

  <script>
    $(".chosen-select").chosen({
      disable_search_threshold: 10,
      width: '100%'
    });

    // For profile pic functionality
    var form = $("#register-form");
    var fileInput = $(".profile-input");
    var button = $(".profile-label");
    var picCont = $(".profile-label .icon-user-cont");
    var filename = $(".filename");
    var instruction = $(".instruction");

    function resetFileInput() {
      fileInput.replaceWith(fileInput.val('').clone(true));
      filename.html("Add a photo to help others identify you");
      instruction.removeClass("photo-selected");
      picCont.html("<i class='icon icon-user'></i>");
      droppedFile = false;
    };

    function newProfilePic(pic, name) {
      picCont.html("<img src='"+pic+"' alt='Profile Picture'>");
      filename.html(name + "<span class='remove ml-xs'><i class='icon icon-cancel'></i></span>");
      instruction.addClass("photo-selected");

      droppedFile = pic;

      $(".remove").click(function(event) {
        event.preventDefault();
        resetFileInput();
      });
    }

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
        var name = this.value.substring(this.value.lastIndexOf('\\') + 1);
        var reader = new FileReader();
        reader.onload = function (e) {
          picCont.html("<img src='"+e.target.result+"' alt='Profile Picture'>");
          newProfilePic(e.target.result, name);
        };
        reader.readAsDataURL(this.files[0]);
      }
    });

    // Drag and Drop

    // Check for Drag and Drop Support on the browser
    var isAdvancedUpload = function() {
      var div = document.createElement('div');
      return (('draggable' in div) || ('ondragstart' in div && 'ondrop' in div)) && 'FormData' in window && 'FileReader' in window;
    }();

    var droppedFile = false;
    if (isAdvancedUpload) {
      button.addClass('has-advanced-upload');

      button.on('drag dragstart dragend dragover dragenter dragleave drop', function(e) {
        e.preventDefault();
        e.stopPropagation();
      })
      .on('dragover dragenter', function() {
        button.addClass('is-dragover');
      })
      .on('dragleave dragend drop', function() {
        button.removeClass('is-dragover');
      })
      .on('drop', function(e) {
        droppedFile = e.originalEvent.dataTransfer.files[0];

        var reader = new FileReader();
        reader.onload = function (e) {
          picCont.html("<img src='"+e.target.result+"' alt='Profile Picture'>");
          newProfilePic(e.target.result, droppedFile.name);
        };
        reader.readAsDataURL(droppedFile);
      });

      form.submit(function(e) {
        e.preventDefault();

        var ajaxData = new FormData(form.get(0));

        if (droppedFile) {
          ajaxData.append('profile', droppedFile);
        }

        $.ajax({
          url: form.attr('action'),
          type: form.attr('method'),
          data: ajaxData,
          dataType: 'json',
          cache: false,
          contentType: false,
          processData: false,
          success: function(response) {
            // Will never reach this point because laravel redirecting is actually an error
            location.reload();
          },
          error: function(error) {
            // TODO: Handle errors. Currently can get all errors, just need to display them

            if (error.status == 200) {
              location.reload();
            } else {
              console.log(error);
              var responseJson = error.responseJSON;
              $.each(responseJson, function() {
                console.log(this[0]);
              });
            }
          }
        });
      });
    }
  </script>
@stop
