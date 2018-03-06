<div class="user card {{ $index == 0 ? 'active' : '' }}" id="user-{{$user->id}}">
  <div class="header {{ $index == 0 ? 'active' : '' }}">
    <div class="left pl-m">
      <span class="title">
        <span class="name mr-xl">
          <span class="profile mr-m">
          @if ($user->profile)
            <img src="{{ $user->getProfilePicUrl() }}" alt="Profile Pic">
          @else
            <i class="icon icon-user-little"></i>
          @endif
          </span>
          @if ($user->first_name) <span class="mr-m">{{$user->first_name}}</span> @endif
          @if ($user->last_name) <span class="mr-m">{{$user->last_name}}</span> @endif
          @if ($user->username) <span class="mr-m username">{{$user->username}}</span> @endif
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
      <span class="mr-xl">Matrix</span>
      <span>{{$user->email}}</span>
    </div>

    <div class="status">
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
    </div>

    <div class="footer">
      <a class="quick-action left delete-user" href="#">
        <span><i class="icon icon-trash"></i></span>
      </a>

      <a class="quick-action underline-middle-hover" href="#">
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
