@extends('app')

@section('content')

    <div class="container">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel panel-default">
                    <div class="panel-body">

                        <h3>Activate a user</h3>

                        {!! Form::open(['method' => 'POST', 'action' => 'Auth\UserController@activator']) !!}

                        <div class="form-group">
                            {!! Form::label('user', 'Username: ') !!}
                            {!! Form::text('user', null, ['class' => 'form-control']) !!}
                        </div>

                        <div class="form-group">
                            {!! Form::label('token', 'Token: ') !!}
                            {!! Form::text('token', null, ['class' => 'form-control']) !!}
                        </div>

                        <div class="form-group">
                            {!! Form::submit('Activate User', ['class' => 'btn btn-primary form-control']) !!}
                        </div>

                        {!! Form::close() !!}

                    </div>
                </div>
            </div>
        </div>
    </div>

@stop