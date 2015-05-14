@extends('app')

@section('leftNavLinks')
    <li><a href="{{ url('/projects/'.$field->pid.'/forms/'.$field->fid) }}">Return to Form</a></li>
@stop

@section('content')
    <h1>Edit Field</h1>

    <hr/>

    {!! Form::model($field,  ['method' => 'PATCH', 'action' => ['FieldController@update',$field->pid, $field->fid]]) !!}
    {!! Form::hidden('fieldId',$field->fieldId,['class' => 'form-control']) !!}
    @include('fields.form',['submitButtonText' => 'Update Field'])
    {!! Form::close() !!}

    @include('errors.list')
@stop