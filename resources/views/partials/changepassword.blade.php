{!! Form::open(['method' => 'PATCH', 'action' => 'Auth\UserController@changepw']) !!}

    <div class="form-group">
        {!! Form::label('new_password', 'New Password:') !!}
        {!! Form::text('new_password', null, ['class' => 'form-control']) !!}

        {!! Form::label('confirm', 'Confirm New Password:') !!}
        {!! Form::text('confirm', null, ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::submit('Submit', ['class' => 'btn btn-primary form-control']) !!}
    </div>

{!! Form::close() !!}

