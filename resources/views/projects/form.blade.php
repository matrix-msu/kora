<div class="form-group">
  {!! Form::label('name', 'Project Name') !!}
  {!! Form::text('name', null, ['class' => 'text-input', 'placeholder' => 'Enter the project name here', 'autofocus']) !!}
</div>

<div class="form-group">
  {!! Form::label('slug', 'Unique Project Identifier') !!}
  {!! Form::text('slug', null, ['class' => 'text-input', 'placeholder' => "Enter the project's unique ID here"]) !!}
</div>

<div class="form-group">
    {!! Form::label('description', 'Description') !!}
    {!! Form::textarea('description', null, ['class' => 'text-area', 'placeholder' => "Enter the projects description here (max. 500 characters)"]) !!}
</div>

@if($projectMode == 'project_create')
<div class="form-group">
    {!! Form::label('admins', trans('projects_form.admin').'(s): ') !!}
    {!! Form::select('admins[]', $users, null, ['class' => 'form-control', 'multiple', 'id' => 'admins']) !!}
</div>
@endif

<div class="form-group">
  <label>Activate Project?</label>

  <div class="check-box">
    <input type="checkbox" value="active" id="active" name="check" />
    <label for="active">
      <span class="check"></span>
      <span class="placeholder">Project set to "inactive"</span>
      <span class="placeholder-alt">Project set to "active"</span>
    </label>
  </div>
</div>

<div class="form-group">
  @if($projectMode == 'project_create')
      {!! Form::submit('Create Project',['class' => 'btn']) !!}
  @elseif($projectMode == 'project_edit')
      {!! Form::submit('Edit Project',['class' => 'btn']) !!}
  @endif
</div>
