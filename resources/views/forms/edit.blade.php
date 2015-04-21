@extends('app')

@section('leftNavLinks')
    <li><a href="{{ url('/projects/'.$form->pid) }}">{{ $projName }}</a></li>
@stop

@section('content')
    <h1>Edit Form</h1>

    <hr/>

    {!! Form::model($form,  ['method' => 'PATCH', 'action' => ['FormController@update',$form->pid]]) !!}
    {!! Form::hidden('fid',$form->fid,['class' => 'form-control']) !!}
    @include('forms.form',['submitButtonText' => 'Update Form', 'nextField' => $form->nextField])
    {!! Form::close() !!}

    @include('errors.list')
@stop