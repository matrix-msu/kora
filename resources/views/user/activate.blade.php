@extends ('app')

@section('content')

    <div class="container">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel panel-default">
                    <div class="panel-heading">

                        {!! Form::open(['method' => 'PATCH', 'action' => 'Auth\UserController@activate']) !!}

                        {!! Form::label('token', trans('user_activate.enter').': ') !!}
                        {!! Form::text('token', null, ['class' => 'form-control']) !!}

                        <br/>

                        {!! Form::submit(trans('user_activate.activate'), ['class' => 'btn btn-primary form-control']) !!}

                    </div>
                </div>
            </div>
        </div>
    </div>

@stop