{!! Form::open(['method' => 'POST', 'action' => ['ProjectGroupController@create', $project->pid]]) !!}

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
        {!! Form::label('create', 'Create Form: ') !!}
        {!! Form::checkbox('create', null, ['class' => 'form-control']) !!}
    </div>

    <div class="form-group" style="display: inline">
        {!! Form::label('edit', 'Edit Form: ') !!}
        {!! Form::checkbox('edit', null, ['class' => 'form-control']) !!}
    </div>

    <div class="form-group" style="display: inline">
        {!! Form::label('create', 'Delete Form: ') !!}
        {!! Form::checkbox('delete', null, ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::submit('Create Project Group', ['class' => 'btn btn-primary form-control']) !!}
    </div>

{!! Form::close() !!}