<div class="form-group mt-xl">
  {!! Form::label('name', 'Project Name') !!}
  {!! Form::text('name', null, ['class' => 'text-input', 'placeholder' => 'Enter the project name here', 'autofocus']) !!}
</div>

<div class="form-group mt-xl">
  {!! Form::label('slug', 'Unique Project Identifier') !!}
  {!! Form::text('slug', null, ['class' => 'text-input', 'placeholder' => "Enter the project's unique ID here (no spaces, alpha-numeric values only)"]) !!}
</div>

<div class="form-group mt-xl">
    {!! Form::label('description', 'Description') !!}
    {!! Form::textarea('description', null, ['class' => 'text-area', 'placeholder' => "Enter the projects description here (max. 500 characters)"]) !!}
</div>

@if($projectMode == 'project_create')
<div class="form-group mt-xl">
    {!! Form::label('admins', 'Select Project Admins') !!}
    {!! Form::select('admins[]', $users, null, [
      'class' => 'multi-select',
      'multiple',
      'data-placeholder' => "Search and select the project admins",
      'id' => 'admins'
    ]) !!}
</div>
@endif

@if($projectMode == 'project_edit')
<div class="form-group mt-xl">
  <div class="spacer"></div>

  <div class="project-permissions">
    <span class="question">Need to Edit Project Permissions?</span>

    <a class="action underline-middle-hover" href="#">
      <span>Go to Project Permissions Page</span>
      <i class="icon icon-arrow-right"></i>
    </a>
  </div>

  <div class="spacer"></div>
</div>
@endif

<!-- <div class="form-group">
  <label>Activate Project?</label>
  <div class="check-box">
    <input type="checkbox" value="1" id="active" class="check-box-input" name="active" />
    <div class="check-box-background"></div>
    <span class="check"></span>
    <span class="placeholder">Project is set to "inactive"</span>
    <span class="placeholder-alt">Project is set to "active"</span>
  </div>
</div> -->

@if($projectMode == 'project_create')
<div class="form-group mt-100-xl">
  {!! Form::submit('Create Project',['class' => 'btn']) !!}
</div>
@elseif($projectMode == 'project_edit')
<div class="form-group project-update-button">
  {!! Form::submit('Update Project',['class' => 'btn edit-btn update-project-submit pre-fixed-js']) !!}
</div>
@endif


@if($projectMode == 'project_edit')
<div class="form-group mt-100-xl">
  <div class="project-cleanup">
    <a class="btn dot-btn archive warning project-archive-js" data-title="Archive Project?" href="#">
      <i class="icon icon-archive"></i>
    </a>

    <a class="btn dot-btn trash warning project-trash-js" data-title="Delete Project?" href="#">
      <i class="icon icon-trash"></i>
    </a>
  </div>
</div>
@endif
