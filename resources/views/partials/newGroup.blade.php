{!! Form::open(['method' => 'POST', 'action' => 'GroupController@create']) !!}

    <div class="form-group">
        {!! Form::label('name', 'Name: ') !!}
        {!! Form::text('name', null, ['class'=>'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('users', 'Users: ') !!}
        {!! Form::select('users[]', $users, null, ['id' => 'users', 'class' => 'form-control', 'multiple']) !!}
    </div>

    <div class="form-group">
        {!! Form::submit('Create Group', ['class' => 'btn btn-primary form-control']) !!}
    </div>

{!! Form::close() !!}