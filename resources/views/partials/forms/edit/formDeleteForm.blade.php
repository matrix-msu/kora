{!! Form::open([
  'method' => 'DELETE',
  'action' => ['FormController@destroy', 'pid' => $form->pid, 'fid' => $form->fid],
  'style' => 'display:none',
  'class' => "delete-content-js"
]) !!}
  <span class="description">
    Are you sure you wish to delete this form? This cannot be undone.
  </span>

  <div class="form-group form-cleanup-submit">
    {!! Form::submit('Delete Form',['class' => 'btn warning']) !!}
  </div>
{!! Form::close() !!}
