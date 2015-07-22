{!! Form::open(['method' => 'POST', 'action' => ['FormGroupController@create', $project->pid]]) !!}

{!! Form::hidden('form', $form->fid, ['class' => 'form-control']) !!}

<div class="form-group">
    {!! Form::label('name', 'Name: ') !!}
    {!! Form::text('name', null, ['class' => 'form-control']) !!}
</div>

<div class="form-group">
    {!! Form::label('users', 'Users: ') !!}
    {!! Form::select('users[]', $users, null, ['id' => 'users', 'class' => 'form-control', 'multiple']) !!}
</div>

{!! Form::label('permissions', 'Group Permissions: ') !!}<br/>

<div class="form-group" style="display: inline">
    {!! Form::label('create', 'Create Field: ') !!}
    {!! Form::checkbox('create', null, ['class' => 'form-control']) !!}
</div>

<div class="form-group" style="display: inline">
    {!! Form::label('edit', 'Edit Field: ') !!}
    {!! Form::checkbox('edit', null, ['class' => 'form-control']) !!}
</div>

<div class="form-group" style="display: inline">
    {!! Form::label('delete', 'Delete Field: ') !!}
    {!! Form::checkbox('delete', null, ['class' => 'form-control']) !!}
</div>

<br/>

<div class="form-group" style="display: inline">
    {!! Form::label('ingest', 'Create Record: ') !!}
    {!! Form::checkbox('ingest', null, ['class' => 'form-control']) !!}
</div>

<div class="form-group" style="display: inline">
    {!! Form::label('modify', 'Edit Record: ') !!}
    {!! Form::checkbox('modify', null, ['class' => 'form-control']) !!}
</div>

<div class="form-group" style="display: inline">
    {!! Form::label('destroy', 'Delete Record: ') !!}
    {!! Form::checkbox('destroy', null, ['class' => 'form-control']) !!}
</div>

<div class="form-group">
    {!! Form::submit('Create Form Group', ['class' => 'btn btn-primary form-control']) !!}
</div>

{!! Form::close() !!}