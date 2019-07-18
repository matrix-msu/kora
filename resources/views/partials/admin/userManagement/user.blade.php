<div class="user card {{ $index == 0 ? 'active' : '' }}" id="user-{{$user->id}}">
  <div class="header {{ $index == 0 ? 'active' : '' }}">
    <div class="left pl-m">
      <span class="title">
        <span class="name mr-xl">
          <span class="profile mr-m">
            @php
              $imgpath = storage_path('app/profiles/' . $user->id . '/' . $user->preferences['profile_pic']);
              $imgurl = $user->getProfilePicUrl();
            @endphp
          @if (File::exists($imgpath) && $user->preferences['profile_pic']!='')
            <img src="{{ $imgurl }}" alt="Profile Pic">
          @else
            <i class="icon icon-user-little"></i>
          @endif
          </span>
          <span class="mr-xxs firstname">{{$user->preferences['first_name']}}</span>
          <span class="mr-m lastname">{{$user->preferences['last_name']}}</span>
          <span class="username">{{$user->username}}</span>
        </span>
      </span>
    </div>

    <div class="card-toggle-wrap">
      <a href="#" class="card-toggle user-toggle-js">
        <i class="icon icon-chevron {{ $index == 0 ? 'active' : '' }}"></i>
      </a>
    </div>
  </div>

  <div class="content content-js {{ $index == 0 ? 'active' : '' }}">
    <div class="organization">
      <span class="attribute">Organization:</span>
      <span class="mr-xl">{{ ($user->preferences['organization'] ? $user->preferences['organization'] : 'None') }}</span>
      <span class="email">{{$user->email}}</span>
    </div>

    <div class="status">
      {!! Form::model($user,  ['method' => 'PATCH', 'action' => ['AdminController@updateStatus', $user->id]]) !!}
        <input name="_token" type="hidden" value="{{ csrf_token() }}"/>
		@if ($user->id != Auth::user()->id)
        <div class="form-group">
          <span>
            <div class="check-box-half check-box-rectangle">
              <input type="checkbox"
                     value="1"
                     class="check-box-input"
                     id="active"
                     name="active"
                     {{$user->active ? 'checked' : ''}}/>
              <span class="check"></span>
              <span class="placeholder">Active</span>
            </div>
          </span>
          <span>
            <div class="check-box-half check-box-rectangle">
              <input type="checkbox"
                     value="1"
                     class="check-box-input"
                     id="admin"
                     name="admin"
                     {{$user->admin ? 'checked' : ''}} />
              <span class="check"></span>
              <span class="placeholder">Admin</span>
            </div>
          </span>
        </div>
		@endif
      {!! Form::close() !!}
    </div>

    <div class="footer">
      @if ($user->id != 1)
        <a class="quick-action left user-trash user-trash-js tooltip" href="#" tooltip="Delete User">
          <i class="icon icon-trash"></i>
        </a>
      @endif

      <a class="quick-action underline-middle-hover" href="{{ url('user/'.$user->id) }}">
        <i class="icon icon-edit-little"></i>
        <span>View User Profile</span>
      </a>

      <a class="quick-action underline-middle-hover" href="{{ url('user/'.$user->id.'/edit') }}">
        <i class="icon icon-edit-little"></i>
        <span>Edit User Profile</span>
      </a>
    </div>
  </div>
</div>
