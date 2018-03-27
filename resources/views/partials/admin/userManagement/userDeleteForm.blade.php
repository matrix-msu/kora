{!! Form::open([
  'method' => 'DELETE',
  'action' => ['AdminController@deleteUser', 'id' => ''],
  'class' => "delete-content-js"
]) !!}
  <span class="description">
    Are you sure you wish to delete this User?
  </span>

  <div class="form-group user-cleanup-submit">
    {!! Form::submit('Delete User',['class' => 'btn warning']) !!}
  </div>
{!! Form::close() !!}
