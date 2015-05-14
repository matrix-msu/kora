@extends('app')

@section('leftNavLinks')
    <li><a href="{{ url('/projects/'.$form->pid.'/forms/'.$form->fid) }}">{{ $form->name }}</a></li>
@stop

@section('content')
    <h1>Create a New Field for {{ $form->name }}</h1>

    <hr/>

    {!! Form::model($field = new \App\Field, ['url' => 'projects/'.$form->pid.'/forms/'.$form->fid]) !!}
        @include('fields.form',['submitButtonText' => 'Create Field'])
    {!! Form::close() !!}

    @include('errors.list')
@stop