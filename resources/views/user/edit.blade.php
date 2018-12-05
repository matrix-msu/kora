@extends('app', ['page_title' => 'Edit User', 'page_class' => 'user-edit'])

@section('aside-content')
  @include('partials.sideMenu.dashboard', ['openDashboardDrawer' => false, 'openProjectDrawer' => false])
@stop

@section('header')
  <section class="head">
    <a class="back" href=""><i class="icon icon-chevron"></i></a>
    <div class="inner-wrap center">
      <h1 class="title">
        <div class="profile-pic-cont profile-pic-cont-js">
          @if ($user->profile)
            <img class="profile-pic profile-pic-js" src="{{ $user->getProfilePicUrl() }}" alt="Profile Pic">
          @else
            <i class="icon icon-user"></i>
          @endif
        </div>
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
      {!! Form::model($user,  ['enctype' => 'multipart/form-data', 'method' => 'PATCH', 'action' => ['AdminController@update', $user->id], 'class' => 'user-form user-form-js form-file-input']) !!}
    @else
      {!! Form::model($user,  ['enctype' => 'multipart/form-data', 'method' => 'PATCH', 'action' => ['Auth\UserController@update', $user->id], 'class' => 'user-form user-form-js form-file-input']) !!}
    @endif
      @include('partials.user.form', ['uid' => $user->id, 'type' => 'edit'])
    {!! Form::close() !!}

    <div class="modal modal-js modal-mask user-delete-modal-js">
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
          @if (\Auth::user()->id == $user->id)
            @include("partials.user.userSelfDeleteForm")
          @else
            {!! Form::open(['method' => 'DELETE', 'action' => ['Auth\UserController@delete', 'uid' => $user->id], 'class' => "delete-content-js"]) !!}
              @include("partials.user.userDeleteForm")
            {!! Form::close() !!}
          @endif
        </div>
      </div>
    </div>

    <div class="modal modal-js modal-mask user-self-delete-modal-js">
      <div class="content small">
        <div class="header">
          <span class="title title-js">Are you Really, Really Sure?</span>
          <a href="#" class="modal-toggle modal-toggle-js">
            <i class="icon icon-cancel"></i>
          </a>
        </div>
        <div class="body">
          {!! Form::open(['method' => 'DELETE', 'action' => ['Auth\UserController@delete', 'uid' => $user->id], 'class' => "self-delete-js"]) !!}
            @include("partials.user.userSelfDeleteActualForm")
          {!! Form::close() !!}
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
      var userid = '{{$user->id}}';
      var redirectUrl = '{{ (\Auth::user()->id == $user->id ? url('/') : url('admin/users')) }}';

      Kora.User.Edit();
  </script>
@stop
