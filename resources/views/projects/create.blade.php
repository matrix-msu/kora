@extends('app')

@section('content')
    <h1>{{trans('projects_create.new')}}</h1>

    <hr/>

    {!! Form::model($project = new \App\Project, ['url' => 'projects']) !!}
    @include('projects.form',['submitButtonText' => trans('projects_create.project')])
    {!! Form::close() !!}

    @include('errors.list')
@stop

@section('footer')
    <script>
        $('#admins').select2();
    </script>
@stop