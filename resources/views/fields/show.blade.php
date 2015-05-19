@extends('app')

@section('leftNavLinks')
    <li>
        <a href="{{ url('/projects/'.$field->pid) }}">{{ $proj->name }}</a>
    </li>
    <li>
        <a href="{{ url('/projects/'.$field->pid.'/forms/'.$field->fid) }}">{{ $form->name }}</a>
    </li>
    <li class="dropdown">
        <a href="#" class="dropdown-toggle" data-toggle="dropdown">{{ $field->name }}<b class="caret"></b></a>
        <ul class="dropdown-menu">
            <li><a href="{{ url('/projects/'.$field->pid) }}">Project Home</a></li>
            <li><a href="{{ url('/projects/'.$field->pid.'/forms/'.$field->fid) }}">Form Home</a></li>
        </ul>
    </li>
@stop

@section('content')
    <span><h1>{{ $field->name }}</h1></span>
    <div><b>Internal Name:</b> {{ $field->slug }}</div>
    <div><b>Description:</b> {{ $field->description }}</div>
    <hr/>
@stop