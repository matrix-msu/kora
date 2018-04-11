@extends('app', ['page_title' => 'Edit User', 'page_class' => 'user-edit'])

@section('header')
  <section class="head">
    <div class="inner-wrap center">
      <h1 class="title">
        @if ($user->profile)
          <img class="head-profile-pic" src="{{ $user->getProfilePicUrl() }}" alt="Profile Pic">
        @else
          <i class="icon icon-user"></i>
        @endif
        <span>Editing {{ $user->first_name }} {{  $user->last_name }}</span>
      </h1>
      @if (\Auth::user()->admin)
          @if ($user->first_name && $user->last_name)
            <p class="description">Edit {{ $user->first_name }} {{ $user->last_name }}'s profile information below, and then select "Update Profile"</p>
          @else
            <p class="description">Edit {{ $user->username }}'s profile information below, and then select "Update Profile"</p>
          @endif
      @else
      <p class="description">Edit your profile information below, and then
        select "Update Profile"</p>
      @endif
    </div>

    @if (\Auth::user()->admin)
      <div class="back">
        <a href="{{ url('admin/users') }}"><p><i class="icon icon-chevron"></i></p></a>
      </div>
    @endif
  </section>
@stop

@section('body')
  <section class="form-container edit-form center">
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

    @if (\Auth::user()->admin && \Auth::user()->id != $user->id)
      {!! Form::model($user,  ['method' => 'PATCH', 'action' => ['AdminController@update', $user->id], 'class' => 'form-file-input']) !!}
    @else
      {!! Form::model($user,  ['method' => 'PATCH', 'action' => ['Auth\UserController@update', $user->id], 'class' => 'form-file-input']) !!}
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
    var CSRFToken = '{{ csrf_token() }}';
    Kora.User.Edit();
  </script>
@stop
