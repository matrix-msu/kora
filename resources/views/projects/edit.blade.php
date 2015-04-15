@extends('app')

@section('content')
    <h1>Edit Project</h1>

    <hr/>

    {!! Form::model($project,  ['method' => 'PATCH', 'action' => ['ProjectController@update', $project->pid]]) !!}
    @include('projects.form',['submitButtonText' => 'Update Project', 'mid' => $project->nextModel])
    {!! Form::close() !!}

    @include('errors.list')
@stop