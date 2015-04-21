@extends('app')

@section('leftNavLinks')
    <li><a href="{{ url('/projects/'.$project->pid) }}">{{ $project->name }}</a></li>
@stop

@section('content')
    <h1>Create a New Form for {{ $project->name }}</h1>

    <hr/>

    {!! Form::model($form = new \App\Form, ['url' => 'projects/'.$project->pid]) !!}
        @include('forms.form',['submitButtonText' => 'Create Form', 'nextField' => 1])
    {!! Form::close() !!}

    @include('errors.list')
@stop