<div class="modal modal-js modal-mask nav-request-permissions-modal-js">
  <div class="content">
    <div class="header">
	  <span class="title">Request Project Permissions</span>
	  <a href="#" class="modal-toggle modal-toggle-js">
		<i class="icon icon-cancel"></i>
	  </a>
	</div>
	<div class="body">

	{!! Form::open(['action' => 'ProjectController@request', 'class' => 'request-project-form-js']) !!}
	<div class="form-group">
		{!! Form::label('request_project', 'Select the Project(s) to Request Permissions') !!}
		<span class="error-message request-error-js"></span>
		{!! Form::select('pids[]', $requestableProjects, null, [
		'class' => 'multi-select request-project-perms-js',
		'multiple',
		'data-placeholder' => "Select the project(s) you would like to request permissions to here    ",
		'id' => 'request_project'
		]) !!}
	</div>

	<div class="form-group request-permissions-submit mt-xxl">
		{!! Form::submit('Request Project Permissions',['class' => 'btn submit-project-request-js']) !!}
	</div>
	{!! Form::close() !!}

	</div>
  </div>
</div>