{!! Form::hidden('pid',$pid) !!}

<div class="form-group">
  {!! Form::label('name', 'Form Name') !!}
  {!! Form::text('name', null, ['class' => 'text-input', 'placeholder' => 'Enter the form name here', 'autofocus']) !!}
</div>

<div class="form-group">
  {!! Form::label('slug', 'Unique Form Identifier') !!}
  {!! Form::text('slug', null, ['class' => 'text-input', 'placeholder' => "Enter the forms's unique ID here (no spaces, alpha-numeric values only)"]) !!}
</div>

<div class="form-group">
  {!! Form::label('description', 'Description') !!}
  {!! Form::textarea('description', null, ['class' => 'text-area', 'placeholder' => "Enter the projects description here (max. 500 characters)"]) !!}
</div>

@if($submitButtonText == 'Create Form')
  <div class="form-group">
    {!! Form::label('admins', 'Select Additional Form Admins') !!}
    {!! Form::select('admins[]', $users, null, [
      'class' => 'multi-select',
      'multiple',
      'data-placeholder' => "Search and select the form admins",
      'id' => 'admins'
    ]) !!}
    <p class="sub-text  mt-xxs">
      Project admins are automatically assigned as admins to this new form, but you may select addition form admins above.
    </p>
  </div>

  <div class="form-group">
    <div class="check-box-half">
      <input type="checkbox" value="1" id="active" class="check-box-input preset-input-js" name="active" />
      <span class="check"></span>
      <span class="placeholder">Apply Form Preset?</span>
    </div>

    <p class="sub-text mt-sm">
      This will apply the form layout structure of the selected form preset to this newly created form.
    </p>
  </div>

  <div class="form-group preset-select-container preset-select-container-js">
    <div class="preset-select-js">
      {!! Form::label('preset', 'Select a Preset') !!}
      {!! Form::select('preset[]', [null=>null] + $presets, null, [
        'class' => 'single-select',
        'data-placeholder' => "Search and select the preset",
        'id' => 'presets'
      ]) !!}
    </div>
  </div>

  <div class="form-group">
    {!! Form::submit($submitButtonText, ['class' => 'btn pre-fixed-js']) !!}
  </div>
@else
  <div class="form-group">
    <label for="preset">Use this Form as a Preset?</label>
    <div class="check-box">
      <input type="checkbox" value="1" id="preset" class="check-box-input" name="preset" {{$form->preset ? 'checked': ''}} />
      <div class="check-box-background"></div>
      <span class="check"></span>
      <span class="placeholder">Form is <strong>not</strong> being used as a preset</span>
      <span class="placeholder-alt">Form is being used as a preset</span>
    </div>

    <p class="sub-text mt-sm">
      Setting this form as a preset will  allow you to apply this forms information and layout structure to a new form.
    </p>
  </div>

  <div class="form-group">
    <div class="spacer"></div>

    <div class="form-permissions">
      <span class="question">Need to Edit Form Permissions?</span>

      <a class="action underline-middle-hover" href="#">
        <span>Go to Form Permissions Page</span>
        <i class="icon icon-arrow-right"></i>
      </a>
    </div>

    <div class="spacer"></div>
  </div>

@if (\Auth::user()->admin || \Auth::user()->isFormAdmin($form))
  <div class="form-group no-padding">

    <div class="form-record-management">
      <p class="title">Test Record Management</p>
      <div class="button-container">
        <a href="#" class="btn half-sub-btn">Create Test Record</a>
        <a href="#" class="btn half-sub-btn warning disabled">Delete All Test Records?</a>
      </div>
    </div>

  </div>

  <div class="form-group">
    <div class="spacer"></div>

    <div class="form-file-size">
      <p class="title">Current Form Filesize - {{$filesize}}</p>
      <div class="button-container">
        <a href="#" class="btn half-sub-btn warning">Delete Old Record Files</a>
        <a href="#" class="btn half-sub-btn warning">Delete All Form Records?</a>
      </div>
    </div>

    <div class="spacer"></div>
  </div>
@endif

  <div class="form-group form-update-button">
    {!! Form::submit('Update Form',['class' => 'btn edit-btn update-form-submit pre-fixed-js']) !!}
  </div>

  <div class="form-group">
    <div class="form-cleanup">
      <a class="btn dot-btn trash warning form-trash-js" data-title="Delete Form?" href="#">
        <i class="icon icon-trash"></i>
      </a>
    </div>
  </div>
@endif
