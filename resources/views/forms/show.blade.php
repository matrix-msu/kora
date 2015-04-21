@extends('app')

@section('leftNavLinks')
    <li><a href="{{ url('/projects/'.$form->pid) }}">{{ $projName }}</a></li>
@stop

@section('content')
    <span><h1>{{ $form->name.' ('.$form->slug.')' }}</h1></span>
    <div>Description: {{ $form->description }}</div>
    <hr/>
    <h2>Fields</h2>
    <!--<formObj>
        We put fields here in a loop
    </formObj> -->
    <!-- This is where we will have the add -->
@stop