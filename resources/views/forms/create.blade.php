@extends('app')

@section('leftNavLinks')
    <li class="dropdown">
        <a href="#" class="dropdown-toggle" data-toggle="dropdown">{{ $project->name }}<b class="caret"></b></a>
        <ul class="dropdown-menu">
            <li><a href="{{ url('/projects/'.$project->pid) }}">Project Home</a></li>
        </ul>
    </li>
@stop

@section('content')
    <h1>Create a New Form for {{ $project->name }}</h1>

    <hr/>

    {!! Form::model($form = new \App\Form, ['url' => 'projects/'.$project->pid]) !!}
        @include('forms.form',['submitButtonText' => 'Create Form', 'pid' => $project->pid])
    {!! Form::close() !!}

    @include('errors.list')
@stop