@extends('app')

@section('leftNavLinks')
    <li><a href="{{ url('/projects/'.$field->pid.'/forms/'.$field->fid) }}">Return to Form</a></li>
@stop

@section('content')
    <span><h1>{{ $field->name }}</h1></span>
    <div><b>Internal Name:</b> {{ $field->slug }}</div>
    <div><b>Description:</b> {{ $field->description }}</div>
    <hr/>
@stop