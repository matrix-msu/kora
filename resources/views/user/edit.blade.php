@extends('app', ['page_title' => 'Edit User', 'page_class' => 'user-edit'])

@section('header')
  <section class="head">
    <div class="inner-wrap center">
      <h1 class="title">
        @if ($user->profile)
          <img class="head-profile-pic" src="{{ $user->getProfilePicUrl() }}" alt="Profile Pic">
        @else
          <i class="icon icon-user-little"></i>
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

    {!! Form::model($user,  ['method' => 'PATCH', 'action' => ['AdminController@update', $user->id]]) !!}
      @include('partials.user.form', ['id' => $user->id, 'type' => 'edit'])
    {!! Form::close() !!}
    </form>
  </section>
@stop


@section('javascripts')
  @include('partials.user.javascripts')

  <script type="text/javascript">
    var CSRFToken = '{{ csrf_token() }}';
    Kora.User.Edit();
  </script>
@stop
