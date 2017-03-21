@extends('app')

@section('content')
    <span><h1>{{trans('projects_index.import')}}</h1></span>

    <hr>

    {!! Form::open(['url' => action('ImportController@importProject'),'enctype' => 'multipart/form-data']) !!}
    <div class="form-group">
        {!! Form::label('project', 'Project (.k3Proj): ') !!}
        {!! Form::file('project', ['class' => 'form-control', 'accept' => '.k3Proj']) !!}
    </div>

    <div class="form-group">
        <button class="form-control btn btn-primary">{{trans('projects_index.importsubmit')}}</button>
    </div>

    {!! Form::close() !!}
@stop

@section('footer')

@stop