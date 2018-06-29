@if (count($requestableProjects) > 0)
  {!! Form::open(['action' => 'ProjectController@request', 'class' => 'nav-request-project-form-js']) !!}
    <div class="form-group">
      {!! Form::label('request_project', 'Select the Project(s) to Request Permissions') !!}
        <span class="error-message nav-request-error-js"></span>
      {!! Form::select('pids[]', $requestableProjects, null, [
        'class' => 'multi-select',
        'multiple',
        'data-placeholder' => "Select the project(s) you would like to request permissions to here    ",
        'id' => 'nav-request_project'
      ]) !!}
    </div>

    <div class="form-group request-permissions-submit mt-xxl">
      {!! Form::submit('Request Project Permissions',['class' => 'btn submit-project-request-js']) !!}
    </div>
  {!! Form::close() !!}
@else
  <div class="request-permissions-error">
    <p class="icon-container">
      <!--  Leave this as a long string -->
      <i class="icon icon-project-happy"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
    </p>
    <p class="text">You already have access to everything!</p>
  </div>

  <div class="form-group submit mt-xxl">
    <a class="btn modal-toggle-js" href="#">Great!</a>
  <div>
@endif
