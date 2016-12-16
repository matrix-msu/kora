@extends('app')

@section('content')
    <span><h1>{{trans('exodus_index.title')}}</h1></span>

    <hr>

    <div class="form-group">
        {{trans('exodus_index.warning')}}
    </div>

    {!! Form::open(['url' => action('ExodusController@migrate'), 'id' => 'k2_form']) !!}

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
        {!! Form::label('filePath', trans('exodus_index.files').': ') !!}
        {!! Form::text('filePath','', ['class' => 'form-control', 'placeholder' => '/{system_path}/{Kora2}/files']) !!}
    </div>

    <div class="form-group" id="k2_submit">
        <button class="form-control btn btn-primary">{{trans('exodus_index.begin')}}</button>
    </div>

    <div style="display:none;" id="search_progress" class="progress">
        <div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%;">
            {{trans('update_index.loading')}}
        </div>
    </div>

    {!! Form::close() !!}

@stop

@section('footer')
    <script>
        $("#k2_form").submit(function(e) { $("#search_progress").slideDown(200); $("#k2_submit").slideUp(200);});
    </script>
@stop