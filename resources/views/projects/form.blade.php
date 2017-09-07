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
    {!! Form::label('admins', 'Select Project Admins') !!}
    {!! Form::select('admins[]', $users, null, [
      'class' => 'multi-select',
      'multiple',
      'data-placeholder' => "Search and select the project admins",
      'id' => 'admins'
    ]) !!}
</div>
@endif

<div class="form-group">
  <label>Activate Project?</label>
  <div class="check-box">
    <input type="checkbox" value="1" id="active" class="check-box-input" name="active" />
    <div class="check-box-background"></div>
    <span class="check"></span>
    <span class="placeholder">Project is set to "inactive"</span>
    <span class="placeholder-alt">Project is set to "active"</span>
  </div>
</div>

<div class="form-group">
  @if($projectMode == 'project_create')
      {!! Form::submit('Create Project',['class' => 'btn']) !!}
  @elseif($projectMode == 'project_edit')
      {!! Form::submit('Edit Project',['class' => 'btn']) !!}
  @endif
</div>
