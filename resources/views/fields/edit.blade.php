@extends('app')

@section('leftNavLinks')
    <li><a href="{{ url('/projects/'.$field->pid.'/forms/'.$field->fid) }}">Return to Form</a></li>
@stop

@section('content')
    <h1>Edit Field</h1>

    <hr/>

    {!! Form::model($field,  ['method' => 'PATCH', 'action' => ['FieldController@update', $field->pid, $field->fid, $field->flid]]) !!}
    @include('fields.form-edit',['submitButtonText' => 'Update Field', 'pid' => $field->pid, 'fid' => $field->fid, 'type' => $field->type, 'required' => $field->required])
    {!! Form::close() !!}

    @include('errors.list')
@stop