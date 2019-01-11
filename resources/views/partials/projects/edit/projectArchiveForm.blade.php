{!! Form::open(['action' => ['ProjectController@setArchiveProject', $project->id], 'style' => 'display:none', 'class' => "archive-content-js"]) !!}
  <span class="description">
    This will hide the project within the “Archived” tab on the projects page. You can unarchive the project at any time from there.
  </span>

  <div class="form-group project-cleanup-submit">
    {!! Form::submit('Archive Project',['class' => 'btn']) !!}
  </div>
{!! Form::close() !!}
