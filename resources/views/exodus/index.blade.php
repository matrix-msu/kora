@extends('app')

@section('content')
    <span><h1>{{trans('exodus_index.title')}}</h1></span>

    <hr>

    <div class="form-group">
        {{trans('exodus_index.warning')}}
    </div>

    {!! Form::open(['url' => action('ExodusController@migrate')]) !!}

    <div class="form-group">
        {!! Form::label('host', trans('exodus_index.host').': ') !!}
        {!! Form::text('host','', ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('name', trans('exodus_index.name').': ') !!}
        {!! Form::text('name','', ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('user', trans('exodus_index.user').': ') !!}
        {!! Form::text('user','', ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('pass', trans('exodus_index.pass').': ') !!}
        {!! Form::password('pass', ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('files', trans('exodus_index.files').': ') !!}
        {!! Form::text('files','', ['class' => 'form-control', 'placeholder' => '/{system_path}/{Kora2}/files']) !!}
    </div>

    <div class="form-group">
        <button class="form-control btn btn-primary">{{trans('exodus_index.begin')}}</button>
    </div>

    {!! Form::close() !!}

@stop

@section('footer')

@stop