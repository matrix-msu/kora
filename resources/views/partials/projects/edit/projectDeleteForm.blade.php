{!! Form::open([
  'method' => 'DELETE',
  'action' => ['ProjectController@destroy', $project->pid],
  'style' => 'display:none',
  'class' => "delete-content-js"
]) !!}
  <span class="description">
    Are you sure you wish to delete this Project? This cannot be undone. 
  </span>

  <div class="form-group project-cleanup-submit">
    {!! Form::submit('Delete Project',['class' => 'btn warning']) !!}
  </div>
{!! Form::close() !!}
