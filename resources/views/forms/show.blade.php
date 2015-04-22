@extends('app')

@section('leftNavLinks')
    <li><a href="{{ url('/projects/'.$form->pid) }}">{{ $projName }}</a></li>
@stop

@section('content')
    <span><h1>{{ $form->name }}</h1></span>
    <div><b>Internal Name:</b> {{ $form->slug }}</div>
    <div><b>Description:</b> {{ $form->description }}</div>
    <hr/>
    <h2>Fields</h2>
    <!--<formObj>
        We put fields here in a loop
    </formObj> -->
    <!-- This is where we will have the add -->
@stop