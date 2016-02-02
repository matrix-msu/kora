<div class="form-group">
    {!! Form::label('name',trans('projects_form.name').': ') !!}
    {!! Form::text('name',null,['class' => 'form-control']) !!}
</div>

<div class="form-group">
    {!! Form::label('slug',trans('projects_form.slug').': ') !!}
    {!! Form::text('slug',null,['class' => 'form-control']) !!}
</div>

<div class="form-group">
    {!! Form::label('description',trans('projects_form.desc').': ') !!}
    {!! Form::textarea('description',null,['class' => 'form-control']) !!}
</div>

@if($submitButtonText == 'Create Project')

<div class="form-group">
    {!! Form::label('admins',trans('projects_form.admin').'(s): ') !!}
    {!! Form::select('admins[]',$users, null,['class' => 'form-control', 'multiple', 'id' => 'admins']) !!}
</div>

@endif

<div class="form-group">
    {!! Form::label('active',trans('projects_form.status').': ') !!}
    {!! Form::select('active', ['1' => trans('projects_form.active'), '0' => trans('projects_form.inactive')], null,['class' => 'form-control']) !!}
</div>

<div class="form-group">
    {!! Form::submit($submitButtonText,['class' => 'btn btn-primary form-control']) !!}
</div>