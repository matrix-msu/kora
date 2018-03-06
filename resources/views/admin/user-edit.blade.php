@extends('app', ['page_title' => 'User Edit', 'page_class' => 'admin-user-edit'])

@section('header')
  <section class="head">
    <div class="inner-wrap center">
      <h1 class="title">
        <i class="icon icon-user"></i>
        <span>Editing First Last</span>
      </h1>
      <p class="description">Edit First Last's profile information below, and then
      select "Update Profile"</p>
    </div>
  </section>
@stop

@section('body')
  <div class="form-container">
    <section class="center">
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

      {!! Form::model($user,  ['method' => 'PATCH', 'action' => ['AdminController@update', $user->id]]) !!}
        @include('partials.admin.form', ['id' => $user->id, 'type' => 'edit'])
      {!! Form::close() !!}

      <form id="admin-user-edit-form" class="form-horizontal" role="form" method="POST" enctype="multipart/form-data" action="{{ action('AdminController@update',['id' => $user->id]) }}">
        <input type="hidden" id="_token" name="_token" value="{{ csrf_token() }}">
        <input type="hidden" id="regtoken" name="regtoken" value="{{\App\Http\Controllers\Auth\RegisterController::makeRegToken()}}">

        <div class="form-group mt-xl">
          <label for="username">Your Username</label>
          <input type="text" class="text-input" id="username" name="username" placeholder="Enter username here" value="{{ $user->username }}">
        </div>

        <div class="form-group mt-xl">
          <label for="email">Your Email</label>
          <input type="email" class="text-input" id="email" name="email" placeholder="Enter email here" value="{{ $user->email }}">
        </div>

        <div class="form-group half mt-xl">
          <label for="first-name">First Name</label>
    			<input type="text" class="text-input" id="first_name" name="first_name" placeholder="Enter first name here" value="{{ $user->first_name }}">
        </div>

        <div class="form-group half mt-xl">
          <label for="first-name">Last Name</label>
    			<input type="text" class="text-input" id="last_name" name="last_name" placeholder="Enter last name here" value="{{ $user->last_name }}">
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
    			<input type="text" class="text-input" id="organization" name="organization" placeholder="Enter organization here" value="{{ $user->organization }}">
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

        <h2 class="mt-xxxl mb-xl">Update Password</h2>

        <div class="form-group mt-xl">
          <label for="password">Enter Current Passowrd</label>
    			<input type="password" class="text-input" id="password" name="password" placeholder="Enter password here">
        </div>

        <div class="form-group mt-xl">
          <label for="password">Enter Current Passowrd</label>
    			<input type="password" class="text-input" id="password" name="password" placeholder="Enter password here">
        </div>

        <div class="form-group mt-xxxl" >
            <button type="submit" class="btn btn-primary">Update Profile</button>
        </div>
      </form>
    </section>
  </div>
@stop


@section('javascripts')
  @include('partials.admin.javascripts')

  <script type="text/javascript">
    var CSRFToken = '{{ csrf_token() }}';
    //Kora.Admin.Users();

    $(".chosen-select").chosen({
      disable_search_threshold: 10,
      width: '100%'
    });
  </script>
@stop
