<section class="head">
    <div class="inner-wrap center">
        <h1 class="title">
            @if ($user->profile)
                <img class="profile-pic" src="{{ $user->getProfilePicUrl() }}" alt="Profile Pic">
            @else
                <i class="icon icon-user"></i>
            @endif
            <span class="ml-m">{{$user->first_name}} {{$user->last_name}}</span>
            @if(\Auth::user()->admin | \Auth::user()->id==$user->id)
                <a href="{{ action('Auth\UserController@editProfile',['uid' => $user->id]) }}" class="head-button tooltip" tooltip="Edit Profile">
                    <i class="icon icon-edit right"></i>
                </a>
            @endif
        </h1>
        <div class="content-sections">
            <div class="content-sections-scroll">
                <a href="{{url('user', ['uid' => $user->id])}}" class="section select-section-js underline-middle underline-middle-hover {{($section == 'profile' ? 'active' : '')}}">Profile</a>
                <a href="{{url('user', ['uid' => $user->id, 'section' => 'permissions'])}}" class="section select-section-js underline-middle underline-middle-hover {{($section == 'permissions' ? 'active' : '')}}">Permissions</a>
                <a href="{{url('user', ['uid' => $user->id, 'section' => 'history'])}}" class="section select-section-js underline-middle underline-middle-hover {{($section == 'history' ? 'active' : '')}}">Record History</a>
            </div>
        </div>
    </div>
</section>