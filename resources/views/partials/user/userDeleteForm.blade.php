{!! Form::open([
  'method' => 'DELETE',
  'action' => ['Auth\UserController@delete', 'id' => '$user->id'],
  'class' => "delete-content-js"
]) !!}
  <span class="description">
    Are you sure you wish to delete your account? This action is non reversible.
    Once deleted, you will be logged out, and unable to sign in again with this account.
  </span>

  <div class="form-group user-cleanup-submit">
    {!! Form::submit('Delete My Account',['class' => 'btn warning']) !!}
  </div>
{!! Form::close() !!}
