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
@endif

<div class="form-group">
  {!! Form::submit($submitButtonText, ['class' => 'btn']) !!}
</div>
