{!! Form::open([
  'method' => 'DELETE',
  'action' => ['ProjectController@destroy', 'pid' => $project->pid],
  'style' => 'display:none',
  'class' => "delete-content-js"
]) !!}
  <span class="description">
    Are you sure you wish to delete this Project?
  </span>

  <div class="form-group project-cleanup-submit">
    {!! Form::submit('Delete Project',['class' => 'btn warning']) !!}
  </div>
{!! Form::close() !!}
