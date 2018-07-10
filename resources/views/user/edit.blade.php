@extends('app', ['page_title' => 'Edit User', 'page_class' => 'user-edit'])

@section('aside-content')
  @include('partials.sideMenu.dashboard', ['openDashboardDrawer' => false, 'openProjectDrawer' => false])
@stop

@section('header')
  <section class="head">
    <a class="back" href="{{ URL::previous() }}"><i class="icon icon-chevron"></i></a>
    <div class="inner-wrap center">
      <h1 class="title">
        @if ($user->profile)
          <img class="profile-pic" src="{{ $user->getProfilePicUrl() }}" alt="Profile Pic">
        @else
          <i class="icon icon-user"></i>
        @endif
        <span>Editing {{ $user->first_name }} {{  $user->last_name }}</span>
      </h1>
      @if (\Auth::user()->admin && \Auth::user()->id != $user->id)
        <p class="description">Edit {{ $user->first_name }} {{ $user->last_name }}'s profile information below, and then select "Update Profile"</p>
      @else
        <p class="description">Edit your profile information below, and then
          select "Update Profile"</p>
      @endif
    </div>
  </section>
@stop

@section('body')
  <section class="form-container edit-form center">

    @if (\Auth::user()->admin && \Auth::user()->id != $user->id)
      {!! Form::model($user,  ['enctype' => 'multipart/form-data', 'method' => 'PATCH', 'action' => ['AdminController@update', $user->id], 'class' => 'user-form form-file-input']) !!}
    @else
      {!! Form::model($user,  ['enctype' => 'multipart/form-data', 'method' => 'PATCH', 'action' => ['Auth\UserController@update', $user->id], 'class' => 'user-form form-file-input']) !!}
    @endif
      @include('partials.user.form', ['uid' => $user->id, 'type' => 'edit'])
    {!! Form::close() !!}

    <div class="modal modal-js modal-mask user-cleanup-modal-js">
      <div class="content small">
        <div class="header">
          @if (\Auth::user()->admin && \Auth::user()->id != $user->id)
            <span class="title title-js">Delete User</span>
          @else
            <span class="title title-js">Delete Account</span>
          @endif
          <a href="#" class="modal-toggle modal-toggle-js">
            <i class="icon icon-cancel"></i>
          </a>
        </div>
        <div class="body">
          @if (\Auth::user()->admin && \Auth::user()->id != $user->id)
            @include("partials.admin.userManagement.userDeleteForm", ['user' => $user])
          @else
            @include("partials.user.userDeleteForm", ['user' => $user])
          @endif
        </div>
      </div>
    </div>
  </section>
@stop


@section('javascripts')
  @include('partials.user.javascripts')

  <script type="text/javascript">
      var validationUrl = '{{action('Auth\UserController@validateUserFields',['uid'=>$user->id])}}';
      var CSRFToken = '{{ csrf_token() }}';
      var userid = {{$user->id}};

      Kora.User.Edit();
  </script>
@stop
