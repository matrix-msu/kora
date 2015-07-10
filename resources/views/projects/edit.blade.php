@extends('app')

@section('content')
    <h1>Edit Project</h1>

    <hr/>

    {!! Form::model($project,  ['method' => 'PATCH', 'action' => ['ProjectController@update', $project->pid]]) !!}
    @include('projects.form',['submitButtonText' => 'Update Project'])
    {!! Form::close() !!}

    @include('errors.list')
@stop

@section('footer')
    <script>
        $('#admins').select2();
    </script>
@stop