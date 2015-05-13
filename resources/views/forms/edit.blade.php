@extends('app')

@section('leftNavLinks')
    <li><a href="{{ url('/projects/'.$form->pid) }}">{{ $projName }}</a></li>
@stop

@section('content')
    <h1>Edit Form</h1>

    <hr/>

    {!! Form::model($form,  ['method' => 'PATCH', 'action' => ['FormController@update',$form->fid]]) !!}
    @include('forms.form',['submitButtonText' => 'Update Form', 'pid' => $form->pid])
    {!! Form::close() !!}

    @include('errors.list')
@stop