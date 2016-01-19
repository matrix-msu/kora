{!! Form::open(['method' => 'PATCH', 'action' => 'Auth\UserController@changepw']) !!}

    <div class="form-group">
        {!! Form::label('new_password', trans('partials_changepassword.new').':') !!}
        {!! Form::password('new_password', ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('confirm', trans('partials_changepassword.confirm').':') !!}
        {!! Form::password('confirm', ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::submit(trans('partials_changepassword.submit'), ['class' => 'btn btn-primary form-control']) !!}
    </div>

{!! Form::close() !!}

