{!! Form::open(['method' => 'PATCH', 'action' => 'Auth\UserController@changepw']) !!}

    <div class="form-group">
        {!! Form::label('new_password', 'New Password:') !!}
        {!! Form::password('new_password', ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('confirm', 'Confirm New Password:') !!}
        {!! Form::password('confirm', ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::submit('Submit', ['class' => 'btn btn-primary form-control']) !!}
    </div>

{!! Form::close() !!}

