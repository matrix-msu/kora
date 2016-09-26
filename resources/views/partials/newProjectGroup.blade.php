{!! Form::open(['method' => 'POST', 'action' => ['ProjectGroupController@create', $project->pid]]) !!}

    <div class="form-group">
        {!! Form::label('name', trans('partials_newProjectGroup.name').': ') !!}
        {!! Form::text('name', null, ['class' => 'form-control']) !!}
    </div>
    <div class="form-group">
        {!! Form::label('users', trans('partials_newProjectGroup.users').': ') !!}
        {!! Form::select('users[]', $users, null, ['id' => 'users', 'class' => 'form-control', 'multiple']) !!}
    </div>

    {!! Form::label('permissions', trans('partials_newProjectGroup.permissions').': ') !!}<br/>

    <div class="form-group" style="display: inline">
        {!! Form::label('create', trans('partials_newProjectGroup.create').': ') !!}
        {!! Form::checkbox('create', null, ['class' => 'form-control']) !!}
    </div>

    <div class="form-group" style="display: inline">
        {!! Form::label('edit', trans('partials_newProjectGroup.edit').': ') !!}
        {!! Form::checkbox('edit', null, ['class' => 'form-control']) !!}
    </div>

    <div class="form-group" style="display: inline">
        {!! Form::label('create', trans('partials_newProjectGroup.delete').': ') !!}
        {!! Form::checkbox('delete', null, ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::submit(trans('partials_newProjectGroup.project'), ['class' => 'btn btn-primary form-control']) !!}
    </div>

{!! Form::close() !!}