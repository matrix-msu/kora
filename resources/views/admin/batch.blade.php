{!! Form::open(['method' => 'PATCH', 'action' => 'AdminController@batch']) !!}

    <div class="form-group">
        {!! Form::label('emails', 'Enter e-mails for batch user creation: ') !!}
        {!! Form::textarea('emails', null, ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::submit('Batch Create Users', ['class' => 'btn btn-primary form-control']) !!}
    </div>

{!! Form::close() !!}