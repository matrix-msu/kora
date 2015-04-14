@extends('app')

@section('content')
    <h1>Create a New Project</h1>

    <hr/>

    {!! Form::model($project = new \App\Project, ['url' => 'projects']) !!}
    @include('projects.form',['submitButtonText' => 'Create Project'])
    {!! Form::close() !!}

    @include('errors.list')
@stop