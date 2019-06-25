<input type="hidden" id="regtoken" name="regtoken" value="{{\App\Http\Controllers\Auth\RegisterController::makeRegToken()}}">

<input type="hidden" id="uid" name="uid" value="{{$user->id}}">

<div class="form-group mt-xl">
  <label for="username">User Name</label>
  <input type="text" class="text-input" id="username" name="username" placeholder="Enter username here" value="{{ $user->username }}">
</div>

<div class="form-group mt-xl">
  <label for="email">Email Address</label>
  <input type="email" class="text-input" id="email" name="email" placeholder="Enter email here" value="{{ $user->email }}">
</div>

<div class="form-group half mt-xl">
  <label for="first-name">First Name</label>
    <span class="error-message">{{array_key_exists("first_name", $errors->messages()) ? $errors->messages()["first_name"][0] : ''}}</span>
  <input type="text" class="text-input {{(array_key_exists("first_name", $errors->messages()) ? ' error' : '')}}"
         id="first_name" name="first_name" placeholder="Enter first name here" value="{{ $user->preferences['first_name'] }}">
</div>

<div class="form-group half mt-xl">
  <label for="first-name">Last Name</label>
    <span class="error-message">{{array_key_exists("last_name", $errors->messages()) ? $errors->messages()["last_name"][0] : ''}}</span>
  <input type="text" class="text-input {{(array_key_exists("last_name", $errors->messages()) ? ' error' : '')}}"
         id="last_name" name="last_name" placeholder="Enter last name here" value="{{ $user->preferences['last_name'] }}">
</div>

<div class="form-group mt-xl">
  <label>Profile Image</label>
  <input type="file" accept="image/*" name="profile" id="profile" class="profile-input" />
  <label for="profile" class="profile-label">
      @php
          $imgpath = storage_path('app/profiles/' . $user->id . '/' . $user->preferences['profile_pic']);
          $imgurl = $user->getProfilePicUrl();
          $photoExists = File::exists($imgpath);
      @endphp
      @if($photoExists)
        <div class="icon-user-cont"><img src="{{ $imgurl }}" alt='Profile Picture'></div>
        <p class="filename">{{ $user->getProfilePicFilename() }}</p>
      @else
        <div class="icon-user-cont"><i class="icon icon-user"></i></div>
        <p class="filename">Add a photo to help others identify you</p>
      @endif
    <p class="instruction mb-0 @if($photoExists) photo-selected @endif">
      <span class="dd">Drag and Drop or Select a Photo here</span>
      <span class="no-dd">Select a Photo here</span>
      <span class="select-new">Select a Different Photo?</span>
    </p>
  </label>
</div>

<div class="form-group mt-xl">
  <label for="organization">Organization</label>
    <span class="error-message">{{array_key_exists("organization", $errors->messages()) ? $errors->messages()["organization"][0] : ''}}</span>
  <input type="text" class="text-input {{(array_key_exists("organization", $errors->messages()) ? ' error' : '')}}"
         id="organization" name="organization" placeholder="Enter organization here" value="{{ $user->preferences['organization'] }}">
</div>

<div class="form-group mt-xl">
    <label for="language">Language</label>
    <select id="language" name="language" class="chosen-select">
        {{$languages_available = getLangs()}}
        @foreach($languages_available->keys() as $lang)
            <option value='{{$languages_available->get($lang)[0]}}'>{{$languages_available->get($lang)[1]}} </option>
        @endforeach
    </select>
</div>

<h2 class="mt-xxxl mb-xl">Update Password</h2>

<div class="form-group mt-xl">
  <label for="new_password">Enter New Password</label>
    <span class="error-message">{{array_key_exists("password", $errors->messages()) ? $errors->messages()["password"][0] : ''}}</span>
  <input type="password" class="text-input {{(array_key_exists("password", $errors->messages()) ? ' error' : '')}}"
         id="password" name="password" placeholder="Enter password here">
</div>

<div class="form-group mt-xl">
  <label for="confirm">Confirm New Password</label>
    <span class="error-message">{{array_key_exists("password_confirmation", $errors->messages()) ? $errors->messages()["password_confirmation"][0] : ''}}</span>
  <input type="password" class="text-input {{(array_key_exists("password_confirmation", $errors->messages()) ? ' error' : '')}}"
         id="password_confirmation" name="password_confirmation" placeholder="Enter password here">
</div>

<div class="form-group mt-100-xl floating-button-height">
    {!! Form::submit('Update Profile', ['class' => 'btn edit-btn update-user-submit pre-fixed-js validate-user-js color-transition']) !!}
</div>

<div class="form-group mt-xxxl">
    @if ($type == 'edit' && $user->id != 1)
        <div class="delete-user">
            <a class="btn dot-btn trash warning user-trash-js tooltip" data-title="Delete User?" href="#" tooltip="Delete User">
                <i class="icon icon-trash"></i>
            </a>
        </div>
    @else
        <div class="spacer invisible"></div>
    @endif
</div>
