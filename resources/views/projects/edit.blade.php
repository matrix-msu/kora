@extends('app', ['page_title' => 'Edit Project'])

@section('content')
    <h1>{{trans('projects_edit.edit')}}</h1>

    <hr/>

    {!! Form::model($project,  ['method' => 'PATCH', 'action' => ['ProjectController@update', $project->pid]]) !!}
    @include('projects.form',['projectMode' => $projectMode])
    {!! Form::close() !!}

    @include('errors.list')
@stop

@section('footer')
    <script>
        $('#admins').select2();
    </script>
@stop
