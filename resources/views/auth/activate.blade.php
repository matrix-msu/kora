@extends('app')

@section('content')

    <div class="container">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel panel-default">
                    <div class="panel-body">

                        <h3>{{trans('auth_activate.activateuser')}}</h3>

                        {!! Form::open(['method' => 'POST', 'action' => 'Auth\UserController@activator']) !!}

                        <div class="form-group">
                            {!! Form::label('user', trans('auth_activate.username').': ') !!}
                            {!! Form::text('user', null, ['class' => 'form-control']) !!}
                        </div>

                        <div class="form-group">
                            {!! Form::label('token', trans('auth_activate.token').': ') !!}
                            {!! Form::text('token', null, ['class' => 'form-control']) !!}
                        </div>

                        <div class="form-group">
                            {!! Form::submit(trans('auth_activate.activateuser'), ['class' => 'btn btn-primary form-control']) !!}
                        </div>

                        {!! Form::close() !!}

                    </div>
                </div>
            </div>
        </div>
    </div>

@stop